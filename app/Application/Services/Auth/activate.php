<?php
// /Proyecto/app/Application/Services/Auth/activate.php
declare(strict_types=1);
require_once __DIR__ . '/../../../Database/db_conexion.php'; // $pdo

$token = $_GET['token'] ?? '';
if (!$token || strlen($token) !== 64) {
    exit('Invalid token.');
}

global $pdo;

// 1) Traer token válido (no usado y no vencido)
$sql = "SELECT at.user_id, at.expires_at, at.used_at, u.status
        FROM activation_tokens at
        JOIN users u ON u.id = at.user_id
        WHERE at.token = :t
        LIMIT 1";
$st = $pdo->prepare($sql);
$st->execute([':t' => $token]);
$row = $st->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    exit('Invalid token.');
}
if (!empty($row['used_at'])) {
    exit('Token already used.');
}
if (new DateTime() > new DateTime($row['expires_at'])) {
    exit('Token expired.');
}

// 2) Activar usuario y marcar token como usado
$pdo->beginTransaction();
try {
    $u = $pdo->prepare("UPDATE users SET status='active' WHERE id=:id");
    $u->execute([':id' => (int)$row['user_id']]);

    $t = $pdo->prepare("UPDATE activation_tokens SET used_at=NOW() WHERE user_id=:id AND token=:t");
    $t->execute([':id' => (int)$row['user_id'], ':t' => $token]);

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    exit('Activation failed, please try again later.');
}

// 3) Mensaje final o redirección a login
echo "Account activated successfully. You can now log in.";
// o si prefieres: header('Location: /Proyecto/app/Views/auth/login.php');
