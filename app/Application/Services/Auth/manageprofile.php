<?php
final class ManageProfile
{
    private const DEFAULT_AVATAR = 'public/assets/img/avatar.png';
    private const USERS_DIR = 'public/assets/img/users';

    /** Raíz del proyecto (la que contiene /public) */
    private static function projectBaseDir(): string
    {
        // Este archivo vive en: app/Application/Services/Auth/manageprofile.php
        // Subimos 4 niveles: .../Proyecto 1
        $base = realpath(__DIR__ . '/../../../../') ?: (__DIR__ . '/../../../../');
        // Si por alguna razón no estamos en la raíz esperada, intenta subir uno más
        if (!is_dir($base . DIRECTORY_SEPARATOR . 'public')) {
            $maybe = realpath($base . '/..') ?: dirname($base);
            if (is_dir($maybe . DIRECTORY_SEPARATOR . 'public')) {
                $base = $maybe;
            }
        }
        return rtrim($base, DIRECTORY_SEPARATOR);
    }

    /** Perfil del usuario */
    public static function getUserProfile(int $userId): array
    {
        global $pdo;
        $sql = "SELECT id, first_name, last_name, email, phone, photo_path, national_id, birth_date
                FROM users
                WHERE id = ?";
        $st = $pdo->prepare($sql);
        $st->execute([$userId]);
        $row = $st->fetch(\PDO::FETCH_ASSOC) ?: [];

        // Normaliza ruta de foto
        if (empty($row['photo_path'])) {
            $row['photo_path'] = self::DEFAULT_AVATAR;
        }
        // Normaliza fechas 'cero'
        if (
            !empty($row['birth_date']) &&
            ($row['birth_date'] === '0000-00-00' || $row['birth_date'] === '0000-00-00 00:00:00')
        ) {
            $row['birth_date'] = '';
        }
        return $row;
    }

    /**
     * Guarda una foto en /public/assets/img/users/ y devuelve la ruta RELATIVA.
     */
    public static function storeProfilePhoto(int $userId, string $tmpPath, string $ext): string
    {
        $ext = strtolower($ext);
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            throw new \RuntimeException('Extensión de imagen no soportada.');
        }

        $baseDir = self::projectBaseDir(); // …/Proyecto 1
        $usersDirAbs = $baseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, self::USERS_DIR);

        if (!is_dir($usersDirAbs) && !@mkdir($usersDirAbs, 0775, true)) {
            throw new \RuntimeException('No se pudo crear el directorio de usuarios.');
        }

        $ext = ($ext === 'jpeg') ? 'jpg' : $ext;
        $filename = 'user_' . $userId . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $targetAbs = $usersDirAbs . DIRECTORY_SEPARATOR . $filename;

        // Guardar archivo
        if (!@move_uploaded_file($tmpPath, $targetAbs)) {
            if (!@rename($tmpPath, $targetAbs)) {
                throw new \RuntimeException('No se pudo guardar la imagen de perfil.');
            }
        }

        // Devuelve REL para BD
        return self::USERS_DIR . '/' . $filename;
    }

    /**
     * Procesa la subida desde $_FILES['photo'] y devuelve la ruta REL nueva (o null si no hay archivo).
     */
    public static function processUploadedPhoto(?array $file, int $userId): ?string
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null; // no se subió archivo
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Error al subir el archivo (código ' . (int) $file['error'] . ').');
        }

        // Tamaño máximo 5MB
        if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
            throw new \RuntimeException('La imagen excede el tamaño máximo permitido (5MB).');
        }

        // Validar tipo MIME real
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => throw new \RuntimeException('Formato no permitido (solo JPG, PNG o WEBP).'),
        };

        return self::storeProfilePhoto($userId, $file['tmp_name'], $ext);
    }

    /**
     * Actualiza perfil. Si $photoFile viene con archivo, lo guarda y elimina la foto anterior (si aplica).
     */
    public static function updateUserProfile(int $userId, array $fields, ?array $photoFile = null): bool
    {
        global $pdo;

        // Foto previa (REL)
        $current = self::getUserProfile($userId);
        $oldPhotoRel = $current['photo_path'] ?? self::DEFAULT_AVATAR;

        // Procesar nueva foto (si hay)
        $newPhotoRel = null;
        if ($photoFile) {
            $newPhotoRel = self::processUploadedPhoto($photoFile, $userId); // puede devolver null
        }

        // Campos permitidos
        $first = trim((string) ($fields['first_name'] ?? ''));
        $last = trim((string) ($fields['last_name'] ?? ''));
        $nid = trim((string) ($fields['national_id'] ?? ''));
        $birth = trim((string) ($fields['birth_date'] ?? ''));
        $email = trim((string) ($fields['email'] ?? ''));
        $phone = trim((string) ($fields['phone'] ?? ''));

        $sql = "UPDATE users
                   SET first_name = :fn,
                       last_name  = :ln,
                       national_id= :nid,
                       birth_date = :bd,
                       email      = :em,
                       phone      = :ph";
        $params = [
            ':fn' => $first,
            ':ln' => $last,
            ':nid' => $nid,
            ':bd' => ($birth !== '' ? $birth : null),
            ':em' => $email,
            ':ph' => $phone,
            ':id' => $userId
        ];

        if ($newPhotoRel !== null) {
            $sql .= ", photo_path = :pp";
            $params[':pp'] = $newPhotoRel;
        }

        $sql .= " WHERE id = :id";

        try {
            $st = $pdo->prepare($sql);
            $success = $st->execute($params);

            // Si se actualizó y hubo nueva foto (distinta del avatar por defecto), borrar la anterior
            if ($success && $newPhotoRel && $oldPhotoRel !== self::DEFAULT_AVATAR) {
                self::deleteOldPhotoIfSafe($oldPhotoRel);
            }
            return $success;
        } catch (\PDOException $e) {
            error_log("PDOException en updateUserProfile: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina vieja foto si está dentro de /public/assets/img/users (por seguridad).
     */
    public static function deleteOldPhotoIfSafe(string $oldRelPath): void
    {
        $normalized = str_replace('\\', '/', $oldRelPath);
        if (!str_starts_with($normalized, self::USERS_DIR . '/')) {
            return; // solo borramos dentro de public/assets/img/users
        }
        $baseDir = self::projectBaseDir(); // …/Proyecto 1
        $abs = $baseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $normalized);
        if (is_file($abs)) {
            @unlink($abs);
        }
    }

    /** Cambia la contraseña con password_hash */
    public static function updatePassword(int $userId, string $currentPlain, string $newPlain): void
    {
        global $pdo;

        $st = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $st->execute([$userId]);
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            throw new \RuntimeException('Usuario no encontrado.');
        }

        $currentHash = (string) $row['password_hash'];
        if (!password_verify($currentPlain, $currentHash)) {
            throw new \RuntimeException('La contraseña actual es incorrecta.');
        }

        $newHash = password_hash($newPlain, PASSWORD_DEFAULT);
        $ok = $pdo->prepare("UPDATE users SET password_hash = :ph, updated_at = NOW() WHERE id = :id")
            ->execute([':ph' => $newHash, ':id' => $userId]);
        if (!$ok) {
            throw new \RuntimeException('No se pudo actualizar la contraseña.');
        }
    }
}
