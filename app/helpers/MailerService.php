<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

function enviarCorreoRegistro(array $datos): bool
{
    $mail = new PHPMailer(true);

    $gmailRemitente = 'jaimecosmeajpi@gmail.com';
    $appPassword    = 'rvuv rtpp votz qwod';

    try {

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $gmailRemitente;
        $mail->Password   = $appPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($gmailRemitente, 'Portal Vantia');

        if (!empty($datos['correo'])) {
            $mail->addAddress($datos['correo'], $datos['nombre'] ?? '');
        }
        $mail->addCC($gmailRemitente, 'Administración Vantia');

        $tipoCuenta = strtoupper(htmlspecialchars($datos['rol'] ?? 'usuario'));
        $nombre     = htmlspecialchars($datos['nombre'] ?? '');
        $correo     = htmlspecialchars($datos['correo'] ?? '');
        $fecha      = date('d/m/Y H:i');

        $filaExtra = '';
        if (($datos['rol'] ?? '') === 'empresa' && !empty($datos['roe'])) {
            $roe = htmlspecialchars($datos['roe']);
            $filaExtra = "<tr><td style='padding:8px; border:1px solid #ddd;'><strong>Número de ROE:</strong></td><td style='padding:8px; border:1px solid #ddd;'>{$roe}</td></tr>";
        } elseif (($datos['rol'] ?? '') === 'empleado' && !empty($datos['telefono'])) {
            $telefono = htmlspecialchars($datos['telefono']);
            $filaExtra = "<tr><td style='padding:8px; border:1px solid #ddd;'><strong>Teléfono:</strong></td><td style='padding:8px; border:1px solid #ddd;'>{$telefono}</td></tr>";
        }

        $mail->isHTML(true);
        $mail->Subject = "Nuevo registro en Vantia ({$tipoCuenta}): {$nombre}";
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;'>
                <div style='background-color: #0f172a; color: #ffffff; padding: 20px;'>
                    <h2 style='margin: 0;'>VANTIA · Nuevo Registro</h2>
                    <p style='margin: 5px 0 0; color: #cbd5e1; font-size: 14px;'>Se ha creado una nueva cuenta en la plataforma.</p>
                </div>
                <div style='padding: 20px; color: #1e293b;'>
                    <table style='width: 100%; border-collapse: collapse; font-size: 14px;'>
                        <tr>
                            <td style='padding: 8px; border: 1px solid #ddd; width: 40%;'><strong>Tipo de cuenta:</strong></td>
                            <td style='padding: 8px; border: 1px solid #ddd;'>{$tipoCuenta}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; border: 1px solid #ddd;'><strong>Nombre / Razón Social:</strong></td>
                            <td style='padding: 8px; border: 1px solid #ddd;'>{$nombre}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; border: 1px solid #ddd;'><strong>Correo registrado:</strong></td>
                            <td style='padding: 8px; border: 1px solid #ddd;'>{$correo}</td>
                        </tr>
                        {$filaExtra}
                        <tr>
                            <td style='padding: 8px; border: 1px solid #ddd;'><strong>Fecha de registro:</strong></td>
                            <td style='padding: 8px; border: 1px solid #ddd;'>{$fecha}</td>
                        </tr>
                    </table>
                    <p style='font-size: 12px; color: #64748b; margin-top: 16px;'>
                        Por seguridad, la contraseña no se incluye en este correo y permanece cifrada en el sistema.
                    </p>
                </div>
            </div>
        ";

        $mail->AltBody = "Nuevo registro ({$tipoCuenta}) - Nombre: {$nombre} | Correo: {$correo} | Fecha: {$fecha}";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo de registro: {$mail->ErrorInfo}");
        return false;
    }
}