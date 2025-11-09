<?php
// /Proyecto/app/Application/Ports/MailSender.php
declare(strict_types=1);

// Cargar PHPMailer sin Composer
require_once __DIR__ . '/../../../lib/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../../../lib/PHPMailer/SMTP.php';
require_once __DIR__ . '/../../../lib/PHPMailer/Exception.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

final class MailSender
{
    /**
     * Envía correo de activación.
     * @return bool true si se envió correctamente
     */
    public static function sendActivationEmail(string $to, string $firstName, string $activateUrl): bool
    {
        $mail = new PHPMailer(true);

        try {
            // ===== SMTP =====
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'jdgomezcubillo2004@gmail.com';           // <-- tu correo
            $mail->Password = 'uxqk liox nhyu uzua';   // <-- contraseña de aplicación
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;     // TLS implícito
            $mail->Port = 465;

            // ↓↓↓ Útiles en dev/local ↓↓↓
            $mail->SMTPDebug = 0;                               // 0 prod, 2 debug
            // Si tu PHP no confía en los certificados locales:
            // $mail->SMTPOptions = [
            //     'ssl' => ['verify_peer'=>false,'verify_peer_name'=>false,'allow_self_signed'=>true]
            // ];

            // ===== Encabezados =====
            $mail->setFrom('tu_correo@gmail.com', 'Aventones');
            $mail->addAddress($to);
            // $mail->addReplyTo('soporte@aventones.com', 'Soporte');

            // ===== Contenido =====
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->Subject = 'Activa tu cuenta - Aventones';
            $mail->Body = self::getActivationEmailBody($firstName, $activateUrl);  // ← tu HTML se mantiene
            $mail->AltBody = self::getActivationEmailText($firstName, $activateUrl);  // ← tu texto se mantiene

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log('Mailer Error: ' . $mail->ErrorInfo);
            return false;
        }
    }

    // ===== Tu misma estructura de email (sin cambios) =====
    private static function getActivationEmailBody(string $firstName, string $activateUrl): string
    {
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
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 15px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <div class='title'>AVENTONES</div>
                    <div style='color: #666;'>Comparte el viaje, comparte la aventura</div>
                </div>
                
                <h2>¡Bienvenido a Aventones!</h2>
                <p>Hola <strong>" . htmlspecialchars($firstName) . "</strong>,</p>
                
                <p>Gracias por registrarte en Aventones. Para activar tu cuenta y comenzar a usar nuestros servicios, haz clic en el siguiente botón:</p>
                
                <p style='text-align: center;'>
                    <a href='{$activateUrl}' class='button'>Activar Mi Cuenta</a>
                </p>
                
                <div class='warning'>
                    <strong>⚠️ Importante:</strong> Este enlace de activación expirará en <strong>24 horas</strong>.
                </div>
                
                <p>Si el botón no funciona, copia y pega esta URL en tu navegador:</p>
                <p style='background: #f8f9fa; padding: 10px; border-radius: 5px; word-break: break-all; font-size: 14px;'>
                    {$activateUrl}
                </p>
                
                <div class='footer'>
                    <p>© " . date('Y') . " Aventones.com. Todos los derechos reservados.</p>
                    <p>Este es un mensaje automático, por favor no respondas a este correo.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    private static function getActivationEmailText(string $firstName, string $activateUrl): string
    {
        return "Activa tu cuenta - Aventones\n\n" .
            "Hola " . $firstName . ",\n\n" .
            "Gracias por registrarte en Aventones. Para activar tu cuenta, por favor visita el siguiente enlace:\n\n" .
            $activateUrl . "\n\n" .
            "Este enlace expirará en 24 horas.\n\n" .
            "Si no te registraste en Aventones, por favor ignora este mensaje.\n\n" .
            "© " . date('Y') . " Aventones.com";
    }
}
