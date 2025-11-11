<?php
final class ManageProfile
{
    // Ajusta según tu estructura
    private const DEFAULT_AVATAR = 'public/assets/img/avatar.png';
    private const USERS_DIR = 'public/assets/img/users';

    /**
     * Obtiene el perfil completo de un usuario.
     */
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
        if (!empty($row['birth_date']) && ($row['birth_date'] === '0000-00-00' || $row['birth_date'] === '0000-00-00 00:00:00')) {
            $row['birth_date'] = '';
        }
        return $row;
    }

    /**
     * Guarda una foto de perfil en /public/assets/img/users/ y devuelve la ruta relativa.
     * @param int $userId
     * @param string $tmpPath ruta temporal subida
     * @param string $ext extensión (jpg/png/webp)
     * @return string ruta relativa donde quedó guardada (ej. public/assets/img/users/user_123.webp)
     */
    public static function storeProfilePhoto(int $userId, string $tmpPath, string $ext): string
    {
        $ext = strtolower($ext);
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            throw new \RuntimeException('Extensión de imagen no soportada.');
        }

        // Asegurar directorio
        $baseDir = realpath(__DIR__ . '/../../../') ?: (__DIR__ . '/../../../');
        $usersDirAbs = $baseDir . '/' . self::USERS_DIR;
        if (!is_dir($usersDirAbs) && !@mkdir($usersDirAbs, 0775, true)) {
            throw new \RuntimeException('No se pudo crear el directorio de usuarios.');
        }

        // Nombre único
        $filename = 'user_' . $userId . '_' . bin2hex(random_bytes(6)) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
        $targetAbs = $usersDirAbs . '/' . $filename;

        if (!@move_uploaded_file($tmpPath, $targetAbs)) {
            // Fallback por si move_uploaded_file falla en entorno CLI
            if (!@rename($tmpPath, $targetAbs)) {
                throw new \RuntimeException('No se pudo guardar la imagen de perfil.');
            }
        }
        // Ruta relativa para guardar en BD
        return self::USERS_DIR . '/' . $filename;
    }



    public static function processUploadedPhoto(?array $file, int $userId): ?string
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null; // no se subió archivo
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Error al subir el archivo (código ' . (int) $file['error'] . ').');
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

        // Guardar imagen usando método existente
        return self::storeProfilePhoto($userId, $file['tmp_name'], $ext);
    }

    /**
     * Actualiza perfil. Si hay nueva foto en $_FILES['photo'], la guarda y elimina la anterior.
     */
    public static function updateUserProfile(int $userId, array $fields, ?array $photoFile = null): bool
    {
        global $pdo;

        // Obtener perfil actual para conocer foto previa
        $current = self::getUserProfile($userId);
        $oldPhotoRel = $current['photo_path'] ?? self::DEFAULT_AVATAR;

        // Procesar la nueva foto (si hay)
        $newPhotoRel = null;
        try {
            if ($photoFile) {
                $newPhotoRel = self::processUploadedPhoto($photoFile, $userId);
            }
        } catch (\Throwable $e) {
            throw new \RuntimeException('Error al procesar la foto: ' . $e->getMessage());
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
                    last_name = :ln,
                    national_id = :nid,
                    birth_date = :bd,
                    email = :em,
                    phone = :ph";
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
            return; // no es una ruta "propia"
        }

        $baseDir = realpath(__DIR__ . '/../../../') ?: (__DIR__ . '/../../../');
        $abs = $baseDir . '/' . $normalized;
        if (is_file($abs)) {
            @unlink($abs);
        }
    }

    /**
     * Cambia la contraseña: valida la actual y guarda la nueva con password_hash.
     * Requiere que la tabla users tenga la columna password_hash.
     */
    public static function updatePassword(int $userId, string $currentPlain, string $newPlain): void
    {
        global $pdo;
        // Obtener hash actual
        $st = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $st->execute([$userId]);
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            throw new \RuntimeException('Usuario no encontrado.');
        }
        $currentHash = (string) $row['password_hash'];

        // Verificar contraseña actual (si existe hash; si tu sistema permite vacía, ajusta)
        if (!password_verify($currentPlain, $currentHash)) {
            throw new \RuntimeException('La contraseña actual es incorrecta.');
        }

        // Generar nuevo hash y guardar
        $newHash = password_hash($newPlain, PASSWORD_DEFAULT);
        $ok = $pdo->prepare("UPDATE users SET password_hash = :ph, updated_at = NOW() WHERE id = :id")
            ->execute([':ph' => $newHash, ':id' => $userId]);
        if (!$ok) {
            throw new \RuntimeException('No se pudo actualizar la contraseña.');
        }
    }

}