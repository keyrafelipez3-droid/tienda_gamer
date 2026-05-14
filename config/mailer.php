<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/mail.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function enviarCodigo2FA($destinatario, $nombre, $codigo) {
    if(!MAIL_ENABLED) {
        // Si el correo no está configurado, mostramos el código en pantalla (modo demo)
        return ['success' => false, 'demo' => true];
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USER;
        $mail->Password   = MAIL_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(MAIL_FROM, MAIL_NAME);
        $mail->addAddress($destinatario, $nombre);
        $mail->isHTML(true);
        $mail->Subject = 'Código de verificación - GamerZone';
        $mail->Body    = "
        <!DOCTYPE html>
        <html>
        <head><meta charset='UTF-8'></head>
        <body style='margin:0;padding:0;background:#070711;font-family:Inter,sans-serif;'>
            <div style='max-width:500px;margin:40px auto;background:#0d0d1a;border:1px solid #1a1a2e;border-radius:20px;overflow:hidden;'>
                <div style='background:linear-gradient(135deg,#0a1f0a,#070711);padding:32px;text-align:center;border-bottom:1px solid #1a1a2e;'>
                    <h1 style='color:#00ff88;font-size:2rem;font-weight:800;margin:0;letter-spacing:2px;'>Gamer<span style='color:#fff;'>Zone</span></h1>
                    <p style='color:#555;margin:8px 0 0;font-size:0.875rem;'>Bolivia</p>
                </div>
                <div style='padding:40px 32px;text-align:center;'>
                    <div style='font-size:2rem;margin-bottom:16px;'>🔐</div>
                    <h2 style='color:#fff;font-size:1.3rem;font-weight:700;margin-bottom:8px;'>Código de Verificación</h2>
                    <p style='color:#555;font-size:0.875rem;margin-bottom:32px;'>Hola <strong style='color:#fff'>$nombre</strong>, aquí está tu código de acceso:</p>
                    <div style='display:flex;justify-content:center;gap:8px;margin-bottom:32px;'>
                        " . implode('', array_map(fn($d) => "<div style='width:52px;height:60px;background:#111120;border:1px solid rgba(0,255,136,0.2);border-radius:12px;display:inline-flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:800;color:#00ff88;margin:0 4px;'>$d</div>", str_split($codigo))) . "
                    </div>
                    <div style='background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);border-radius:12px;padding:16px;margin-bottom:24px;'>
                        <p style='color:#f59e0b;font-size:0.82rem;margin:0;'><strong>⚠️ Este código expira en 10 minutos.</strong><br>No lo compartas con nadie.</p>
                    </div>
                    <p style='color:#444;font-size:0.78rem;'>Si no solicitaste este código, ignora este mensaje.</p>
                </div>
                <div style='background:#0a0a14;padding:20px;text-align:center;border-top:1px solid #1a1a2e;'>
                    <p style='color:#333;font-size:0.75rem;margin:0;'>© 2025 GamerZone Bolivia — Todos los derechos reservados</p>
                </div>
            </div>
        </body>
        </html>";

        $mail->send();
        return ['success' => true, 'demo' => false];
    } catch(Exception $e) {
        return ['success' => false, 'demo' => false, 'error' => $e->getMessage()];
    }
}

function enviarBienvenida($destinatario, $nombre) {
    if(!MAIL_ENABLED) return ['success' => false, 'demo' => true];

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USER;
        $mail->Password   = MAIL_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(MAIL_FROM, MAIL_NAME);
        $mail->addAddress($destinatario, $nombre);
        $mail->isHTML(true);
        $mail->Subject = '¡Bienvenido a GamerZone Bolivia!';
        $mail->Body    = "
        <!DOCTYPE html>
        <html>
        <body style='margin:0;padding:0;background:#070711;font-family:Inter,sans-serif;'>
            <div style='max-width:500px;margin:40px auto;background:#0d0d1a;border:1px solid #1a1a2e;border-radius:20px;overflow:hidden;'>
                <div style='background:linear-gradient(135deg,#0a1f0a,#070711);padding:32px;text-align:center;border-bottom:1px solid #1a1a2e;'>
                    <h1 style='color:#00ff88;font-size:2rem;font-weight:800;margin:0;'>Gamer<span style='color:#fff;'>Zone</span></h1>
                </div>
                <div style='padding:40px 32px;text-align:center;'>
                    <div style='font-size:3rem;margin-bottom:16px;'>🎮</div>
                    <h2 style='color:#fff;font-size:1.4rem;font-weight:800;margin-bottom:12px;'>¡Bienvenido, $nombre!</h2>
                    <p style='color:#555;font-size:0.9rem;line-height:1.7;margin-bottom:24px;'>Tu cuenta en GamerZone ha sido creada exitosamente. Ahora puedes explorar nuestro catálogo y encontrar el equipo gamer perfecto para ti.</p>
                    <a href='http://localhost:8080/tienda_gamer/views/cliente/productos.php' style='display:inline-block;background:#00ff88;color:#000;font-weight:700;border-radius:12px;padding:14px 32px;text-decoration:none;font-size:1rem;'>Explorar Tienda</a>
                </div>
                <div style='background:#0a0a14;padding:20px;text-align:center;border-top:1px solid #1a1a2e;'>
                    <p style='color:#333;font-size:0.75rem;margin:0;'>© 2025 GamerZone Bolivia</p>
                </div>
            </div>
        </body>
        </html>";

        $mail->send();
        return ['success' => true];
    } catch(Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}