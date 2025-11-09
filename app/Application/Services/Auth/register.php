<?php
// /Proyecto/app/Application/Services/Auth/register.php
declare(strict_types=1);

require_once __DIR__ . '/../../../Database/db_conexion.php'; // $pdo
require_once __DIR__ . '/../../../Application/Ports/MailSender.php';    // MailSender

final class RegisterService
{
    /** Registrar PASAJERO */
    public static function registerPassenger(array $post, array $files = []): int
    {
        return self::registerUser($post, $files, 'passenger');
    }

    /** Registrar CHOFER */
    public static function registerDriver(array $post, array $files = []): int
    {
        return self::registerUser($post, $files, 'driver');
    }

    // -------- Internos --------

    private static function registerUser(array $post, array $files, string $role): int
    {
        global $pdo;

        // 1) Validaciones mínimas
        $required = ['first_name', 'last_name', 'national_id', 'birth_date', 'email', 'phone', 'password', 'password2'];
        foreach ($required as $f) {
            if (!isset($post[$f]) || trim((string) $post[$f]) === '') {
                throw new RuntimeException("Missing field: $f");
            }
        }
        if (!filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException("Invalid email.");
        }
        if ((string) $post['password'] !== (string) $post['password2']) {
            throw new RuntimeException("Passwords do not match.");
        }

        // 2) Unicidad de correo
        $q = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $q->execute([strtolower(trim($post['email']))]);
        if ((int) $q->fetchColumn() > 0) {
            throw new RuntimeException("Email already registered.");
        }

        // 3) Subida de foto (opcional)
        $photoPath = null;
        if (!empty($files['photo']['tmp_name']) && is_uploaded_file($files['photo']['tmp_name'])) {
            $ext = strtolower(pathinfo($files['photo']['name'], PATHINFO_EXTENSION) ?: 'jpg');
            $dir = __DIR__ . '/../../../../public/assets/img/users/';
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            $fname = 'u_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
            if (!move_uploaded_file($files['photo']['tmp_name'], $dir . $fname)) {
                throw new RuntimeException("Could not save photo.");
            }
            $photoPath = 'public/assets/img/users/' . $fname; // ruta pública
        }

        // 4) Insertar usuario (status pending)
        $pwdHash = password_hash((string) $post['password'], PASSWORD_BCRYPT);

        $sql = "INSERT INTO users
                (role, status, first_name, last_name, national_id, birth_date, email, phone, photo_path, password_hash)
                VALUES
                (:role, 'pending', :first_name, :last_name, :national_id, :birth_date, :email, :phone, :photo_path, :password_hash)";
        $st = $pdo->prepare($sql);
        $st->execute([
            ':role' => $role,
            ':first_name' => trim((string) $post['first_name']),
            ':last_name' => trim((string) $post['last_name']),
            ':national_id' => trim((string) $post['national_id']),
            ':birth_date' => (string) $post['birth_date'],
            ':email' => strtolower(trim((string) $post['email'])),
            ':phone' => trim((string) $post['phone']),
            ':photo_path' => $photoPath,
            ':password_hash' => $pwdHash,
        ]);

        $userId = (int) $pdo->lastInsertId();

        // 5) Crear token de activación en activation_tokens (64 chars, 24h)
        $token = bin2hex(random_bytes(32)); // 64 hex chars
        $expiresAt = (new DateTime('+24 hours'))->format('Y-m-d H:i:s');

        $stTok = $pdo->prepare(
            "INSERT INTO activation_tokens (user_id, token, expires_at, used_at)
                    VALUES (:uid, :token, :exp, NULL)"
        );
        $stTok->execute([
            ':uid' => $userId,
            ':token' => $token,
            ':exp' => $expiresAt,
        ]);

        // 6) Enviar correo con enlace de activación
        $baseUrl = self::guessBaseUrl();
        $activateUrl = $baseUrl . "/app/Application/Services/Auth/activate.php?token={$token}";

        MailSender::sendActivationEmail((string) $post['email'], $post['first_name'], $activateUrl);
        return $userId;
    }

    /** Deducción simple del base URL; ajusta si tu proyecto corre en otra ruta/host */
    private static function guessBaseUrl(): string
    {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

        // Ruta raíz del proyecto en URL (ajústala si lo necesitas)
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/';
        // /Proyecto/app/Application/Services/Auth/register.php  -> queremos /Proyecto
        $parts = explode('/app/', $scriptName, 2);
        $projectBase = $parts[0] ?? '';
        if ($projectBase === '')
            $projectBase = '/Proyecto'; // fallback

        return "{$scheme}://{$host}{$projectBase}";
    }
}
