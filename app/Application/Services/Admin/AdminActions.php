<?php
// app/Application/Services/Admin/AdminActions.php
declare(strict_types=1);

// conexión a la base de datos
require_once __DIR__ . '/../../../Database/db_conexion.php'; // conexión PDO $pdo

class AdminActions
{
    /** Crear nuevo admin con fecha actual */
    public static function createAdmin(array $data, ?array $file = null): bool
    {
        global $pdo;

        foreach (['first_name', 'last_name', 'email', 'password'] as $f) {
            if (empty($data[$f])) {
                throw new Exception("Missing field: $f");
            }
        }

        if (empty($data['password2'])) {
            throw new Exception("Missing field: password2");
        }
        if ($data['password'] !== $data['password2']) {
            throw new Exception("Passwords do not match");
        }
        if (strlen($data['password']) < 6) {
            throw new Exception("Password must be at least 6 characters");
        }

        $pwdHash = password_hash($data['password'], PASSWORD_BCRYPT);

        // Guardar foto (opcional)
        $photoPath = null;
        if ($file && !empty($file['tmp_name'])) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg');
            $destDir = __DIR__ . '/../../../../public/assets/img/users/';
            if (!is_dir($destDir)) {
                @mkdir($destDir, 0775, true);
            }
            $filename = 'admin_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $destDir . $filename)) {
                $photoPath = 'public/assets/img/users/' . $filename;
            }
        }

        // Insert con fecha de creación
        $sql = "INSERT INTO users
                    (role, status, first_name, last_name, national_id, birth_date, email, phone, photo_path, password_hash, created_at)
                VALUES
                    ('admin', 'active', :first_name, :last_name, :national_id, :birth_date, :email, :phone, :photo_path, :password_hash, NOW())";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':first_name' => $data['first_name'],
            ':last_name' => $data['last_name'],
            ':national_id' => $data['national_id'] ?? null,
            ':birth_date' => $data['birth_date'] ?? null,
            ':email' => $data['email'],
            ':phone' => $data['phone'] ?? null,
            ':photo_path' => $photoPath,
            ':password_hash' => $pwdHash,
        ]);
    }

    /** Obtener todos los usuarios con filtros */
    public static function getUsers(string $roleFilter = 'all', string $statusFilter = 'all'): array
    {
        global $pdo;

        $sql = "SELECT id, first_name, last_name, email, role, status, created_at
                FROM users
                WHERE 1=1";

        $params = [];

        // Filtro por rol
        if ($roleFilter !== 'all') {
            $sql .= " AND role = :role";
            $params[':role'] = $roleFilter;
        }

        // Filtro por estado
        if ($statusFilter !== 'all') {
            $sql .= " AND status = :status";
            $params[':status'] = $statusFilter;
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Actualizar estado de usuario */
    public static function updateUserStatus(int $id, string $status): bool
    {
        global $pdo;

        // 1) Proteger al primer admin (el de menor id)
        $minId = (int) $pdo->query("SELECT MIN(id) FROM users WHERE role='admin'")->fetchColumn();
        if ($id === $minId) {
            return false; // es el admin semilla
        }

        // Validar que el estado sea válido
        $validStatuses = ['active', 'inactive'];
        if (!in_array($status, $validStatuses)) {
            throw new Exception("Invalid status");
        }

        // No permitir desactivar todos los admins
        if ($status === 'inactive') {
            $user = $pdo->prepare("SELECT role FROM users WHERE id = :id")->execute([':id' => $id]);
            $userData = $pdo->prepare("SELECT role FROM users WHERE id = :id")->fetch(PDO::FETCH_ASSOC);

            if ($userData && $userData['role'] === 'admin') {
                $activeAdmins = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='admin' AND status='active'")->fetchColumn();
                if ($activeAdmins <= 1) {
                    return false; // No se puede desactivar el último admin activo
                }
            }
        }

        $sql = "UPDATE users SET status = :status WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':status' => $status,
            ':id' => $id
        ]);
    }

    /** Eliminar un administrador (protege contra borrar el último) */
    public static function deleteAdmin(int $id): bool
    {
        global $pdo;

        // 1) No dejar al sistema sin admins
        $count = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
        if ($count <= 1) {
            return false; // queda al menos uno
        }

        // 2) Proteger al primer admin (el de menor id)
        $minId = (int) $pdo->query("SELECT MIN(id) FROM users WHERE role='admin'")->fetchColumn();
        if ($id === $minId) {
            return false; // es el admin semilla
        }

        // 3) Obtener ruta de la foto (si existe) antes de borrar
        $stmt = $pdo->prepare("SELECT photo_path FROM users WHERE id = :id AND role = 'admin' LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return false; // no existe ese admin
        }
        $photoPath = $row['photo_path'];

        // 4) Borrar admin de la BD
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id AND role = 'admin'");
        $deleted = $stmt->execute([':id' => $id]);

        // 5) Si se borró en BD, intentar eliminar fichero de foto (con comprobaciones de seguridad)
        if ($deleted && !empty($photoPath)) {
            $allowedPrefix = 'public/assets/img/users/';
            // sólo operar sobre rutas relativas esperadas para evitar eliminar ficheros arbitrarios
            if (strpos($photoPath, $allowedPrefix) === 0) {
                $fullPath = __DIR__ . '/../../../../' . $photoPath;
                // asegurarnos de que la ruta real esté dentro del directorio de usuarios
                $assetsDir = realpath(__DIR__ . '/../../../../public/assets/img/users/');
                $realFull = realpath($fullPath) ?: $fullPath;
                if ($assetsDir && strpos($realFull, $assetsDir) === 0 && is_file($realFull)) {
                    @unlink($realFull);
                }
            }
        }

        return (bool) $deleted;
    }

}