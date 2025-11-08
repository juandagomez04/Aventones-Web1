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

    /** Obtener todos los administradores (ordenados por fecha) */
    public static function getAdmins(): array
    {
        global $pdo;
        $sql = "SELECT id, first_name, last_name, email, status, created_at
                FROM users
                WHERE role = 'admin'
                ORDER BY created_at DESC";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

        // 3) Borrar admins normales
        $stmt = $pdo->prepare("DELETE FROM users WHERE id=:id AND role='admin'");
        return $stmt->execute([':id' => $id]);
    }

}
