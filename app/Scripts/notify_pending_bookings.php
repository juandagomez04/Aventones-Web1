<?php
// /Proyecto/app/Scripts/notify_pending_bookings.php
declare(strict_types=1);

require_once __DIR__ . '/../Application/Ports/MailSender.php'; // usa tu MailSender (PHPMailer Gmail)

// ===== DB =====
$DB_HOST = '127.0.0.1';
$DB_NAME = 'aventones';
$DB_USER = 'root';
$DB_PASS = '';
$DB_CHARSET = 'utf8mb4';
$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=$DB_CHARSET";

// ===== parámetro minutos (default 1) =====
$minutes = isset($argv[1]) && is_numeric($argv[1]) ? (int) $argv[1] : 1;
echo "⏱  Buscando reservas 'pending' con más de {$minutes} minuto(s)...\n";

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // SIN r.depart_at (no existe en tu esquema). Solo origin/destination.
    $sql = "
        SELECT 
            b.id               AS booking_id,
            b.created_at       AS booking_created_at,
            r.id               AS ride_id,
            r.origin,
            r.destination,
            u.id               AS driver_id,
            u.email            AS driver_email,
            u.first_name       AS driver_first_name,
            u.last_name        AS driver_last_name
        FROM bookings b
        INNER JOIN rides r ON r.id = b.ride_id
        INNER JOIN users u ON u.id = r.driver_id
        WHERE b.status = 'pending'
            AND b.created_at <= (NOW() - INTERVAL :minutes MINUTE)
        ORDER BY b.created_at ASC
        LIMIT 1000
    ";

    $st = $pdo->prepare($sql);
    $st->bindValue(':minutes', $minutes, PDO::PARAM_INT);
    $st->execute();
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
        echo "✅ No se encontraron reservas pendientes con ese criterio.\n";
        exit(0);
    }

    $sent = 0;
    foreach ($rows as $row) {
        $to = trim((string) $row['driver_email']);
        if ($to === '') {
            fwrite(STDERR, "⚠️  Booking #{$row['booking_id']} sin email de chofer. Omitido.\n");
            continue;
        }

        // asunto y cuerpos (sin depart_at)
        $subject = "Tienes solicitudes de reserva pendientes - Aventones";
        $html = getEmailBody(
            $row['driver_first_name'],
            $row['driver_last_name'],
            $row['origin'],
            $row['destination'],
            (string) $row['booking_id'],
            (string) $row['booking_created_at']
        );
        $text = getEmailText(
            $row['driver_first_name'],
            $row['origin'],
            $row['destination'],
            (string) $row['booking_id']
        );

        // Usa la misma lógica SMTP que MailSender (ya incluida en MailSender)
        // Creamos un PHPMailer como en tu clase, pero más simple: reutilizamos MailSender enviando "activation" style
        // Como MailSender está especializado a activación, aquí construimos nuestro propio PHPMailer con misma config:
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'jdgomezcubillo2004@gmail.com';
            $mail->Password = 'uxqk liox nhyu uzua'; // contraseña de aplicación
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->setFrom('jdgomezcubillo2004@gmail.com', 'Aventones');
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $html;
            $mail->AltBody = $text;

            $mail->send();
            echo "📧 Enviado a {$to} por booking #{$row['booking_id']}\n";
            $sent++;
        } catch (Throwable $e) {
            echo "❌ Error enviando a {$to}: {$mail->ErrorInfo}\n";
        }
    }

    echo "✅ Correos enviados: {$sent}\n";
} catch (Throwable $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    exit(1);
}

// ======= templates de email (sin depart_at) =======
function getEmailBody(
    string $firstName,
    string $lastName,
    string $origin,
    string $destination,
    string $bookingId,
    string $createdAt
): string {
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .header { text-align: center; margin-bottom: 30px; }
            .title { color: #2c5aa0; font-size: 28px; font-weight: bold; }
            .button { background: #7187a8ff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold; }
            .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #666; font-size: 12px; }
            ul { line-height: 1.6; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <div class='title'>AVENTONES</div>
                <div style='color:#666;'>Comparte el viaje, comparte la aventura</div>
            </div>

            <h2>¡Tienes solicitudes de reserva pendientes!</h2>
            <p>Hola <strong>{$firstName} {$lastName}</strong>,</p>
            <p>Se detectó una reserva pendiente que aún no has gestionado:</p>

            <ul>
                <li><strong>Booking ID:</strong> {$bookingId}</li>
                <li><strong>Ruta:</strong> {$origin} → {$destination}</li>
                <li><strong>Fecha de solicitud:</strong> {$createdAt}</li>
            </ul>

            <p>Ingresa a Aventones para <strong>aceptar o rechazar</strong> la solicitud.</p>

            <p style='text-align:center;margin-top:24px;'>
                <a class='button' href='http://localhost/Proyecto/app/Views/myrides/myrides.php'>Revisar Mis Rides</a>
            </p>

            <div class='footer'>
                <p>© " . date('Y') . " Aventones.com. Todos los derechos reservados.</p>
                <p>Este es un mensaje automático, por favor no respondas a este correo.</p>
            </div>
        </div>
    </body>
    </html>";
}

function getEmailText(
    string $firstName,
    string $origin,
    string $destination,
    string $bookingId
): string {
    return "Aventones - Reservas pendientes\n\n"
        . "Hola {$firstName},\n\n"
        . "Tienes una solicitud de reserva pendiente en la ruta {$origin} → {$destination}.\n"
        . "Booking ID: {$bookingId}\n\n"
        . "Ingresa al sistema para aceptarla o rechazarla.\n\n"
        . "© " . date('Y') . " Aventones.com";
}
