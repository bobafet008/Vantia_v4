<?php


require_once __DIR__ . '/../helpers/mail.php';

class SoporteController {

    public function index() {
        // Cargar la vista de soporte
        require_once __DIR__ . '/../../views/soporte.php';
    }

    public function enviarConsulta() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre  = trim($_POST['nombre'] ?? '');
            $email   = trim($_POST['email'] ?? '');
            $rol     = trim($_POST['rol'] ?? 'Usuario');
            $asunto  = trim($_POST['asunto'] ?? 'Consulta general');
            $mensaje = trim($_POST['mensaje'] ?? '');

            
            if (empty($nombre) || empty($email) || empty($mensaje)) {
                $_SESSION['error_soporte'] = "Por favor completa todos los campos obligatorios.";
                header("Location: ban.php?pagina=soporte");
                exit;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error_soporte'] = "Por favor ingresa una dirección de correo válida.";
                header("Location: ban.php?pagina=soporte");
                exit;
            }

            // Guardar registro de la consulta de atención al cliente en la base de datos
            require __DIR__ . '/../../config/database.php';
            require_once __DIR__ . '/../Models/MensajeSoporte.php';
            require_once __DIR__ . '/../Models/RegistroAdmin.php';

            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            try {
                $usuarioMayId = null;
                $usuarioMenId = null;
                $empresaId = null;
                $emprendimientoId = null;

                // 1. Identificar por sesion activa
                if (!empty($_SESSION['usuario_id']) && !empty($_SESSION['tabla_usuario'])) {
                    $tablaSesion = $_SESSION['tabla_usuario'];
                    $idSesion = (int) $_SESSION['usuario_id'];
                    if ($tablaSesion === 'usuarios_may') {
                        $usuarioMayId = $idSesion;
                    } elseif ($tablaSesion === 'usuarios_men') {
                        $usuarioMenId = $idSesion;
                    } elseif ($tablaSesion === 'empresas') {
                        $empresaId = $idSesion;
                    } elseif ($tablaSesion === 'emprendimientos') {
                        $emprendimientoId = $idSesion;
                    }
                }

                // 2. Si no se obtuvo por sesion, buscar cuenta por correo electronico
                if (!$usuarioMayId && !$usuarioMenId && !$empresaId && !$emprendimientoId) {
                    $stmtMay = $conexion->prepare("SELECT id FROM usuarios_may WHERE correo = ? LIMIT 1");
                    $stmtMay->execute([$email]);
                    $rowMay = $stmtMay->fetch(PDO::FETCH_ASSOC);
                    if ($rowMay) {
                        $usuarioMayId = (int) $rowMay['id'];
                    } else {
                        $stmtMen = $conexion->prepare("SELECT id FROM usuarios_men WHERE correo = ? LIMIT 1");
                        $stmtMen->execute([$email]);
                        $rowMen = $stmtMen->fetch(PDO::FETCH_ASSOC);
                        if ($rowMen) {
                            $usuarioMenId = (int) $rowMen['id'];
                        } else {
                            $stmtEmpr = $conexion->prepare("SELECT id FROM emprendimientos WHERE correo = ? LIMIT 1");
                            $stmtEmpr->execute([$email]);
                            $rowEmpr = $stmtEmpr->fetch(PDO::FETCH_ASSOC);
                            if ($rowEmpr) {
                                $emprendimientoId = (int) $rowEmpr['id'];
                            } else {
                                $stmtEmp = $conexion->prepare("SELECT id FROM empresas WHERE correo = ? LIMIT 1");
                                $stmtEmp->execute([$email]);
                                $rowEmp = $stmtEmp->fetch(PDO::FETCH_ASSOC);
                                if ($rowEmp) {
                                    $empresaId = (int) $rowEmp['id'];
                                }
                            }
                        }
                    }
                }

                $modeloSoporte = new MensajeSoporte($conexion);
                $mensajeSoporteId = $modeloSoporte->crear(
                    $nombre, $email, $rol, $asunto, $mensaje, $ip,
                    $usuarioMayId, $usuarioMenId, $empresaId, $emprendimientoId
                );

                // Registrar en bitacora de auditoria general vinculando soporte y usuario
                $regAdmin = new RegistroAdmin($conexion);
                $regAdmin->registrar(
                    null,
                    'soporte',
                    'Consulta Atención al Cliente (' . substr($asunto, 0, 18) . ')',
                    substr("Gmail de $nombre ($email): $asunto", 0, 80),
                    $email,
                    $ip,
                    [
                        'mensaje_soporte_id' => $mensajeSoporteId ?: null,
                        'usuario_may_id'     => $usuarioMayId,
                        'usuario_men_id'     => $usuarioMenId,
                        'empresa_id'         => $empresaId,
                        'emprendimiento_id'  => $emprendimientoId,
                    ]
                );
            } catch (Exception $e) {
                error_log("Error al registrar consulta de soporte: " . $e->getMessage());
            }

            $config = require __DIR__ . '/../../config/mail.php';
            $correoAdmin = $config['usuario'];

            $asuntoMail = "[Atención al Cliente] $asunto - ($rol)";
            $cuerpoHTML = "
                <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px; max-width: 600px;'>
                    <h2 style='color: #2b6cb0; margin-top: 0;'>Nueva Consulta de Atención al Cliente</h2>
                    <p><strong>Nombre:</strong> " . htmlspecialchars($nombre) . "</p>
                    <p><strong>Correo del Usuario:</strong> " . htmlspecialchars($email) . "</p>
                    <p><strong>Tipo de Usuario:</strong> " . htmlspecialchars($rol) . "</p>
                    <p><strong>Asunto:</strong> " . htmlspecialchars($asunto) . "</p>
                    <hr style='border: none; border-top: 1px solid #e2e8f0;'>
                    <p><strong>Mensaje:</strong></p>
                    <blockquote style='background: #f7fafc; padding: 12px; border-left: 4px solid #2b6cb0; margin: 0;'>
                        " . nl2br(htmlspecialchars($mensaje)) . "
                    </blockquote>
                    <hr style='border: none; border-top: 1px solid #e2e8f0; margin-top: 20px;'>
                    <p style='font-size: 12px; color: #718096;'>Puedes responder a este correo directamente para escribirle al usuario.</p>
                </div>
            ";

            $exito = enviarCorreo($correoAdmin, "Admin Vantia", $asuntoMail, $cuerpoHTML, $email, $nombre);

            if ($exito) {
                $_SESSION['exito_soporte'] = "¡Tu mensaje ha sido enviado correctamente! El equipo de soporte técnico se pondrá en contacto contigo pronto.";
            } else {
                $_SESSION['error_soporte'] = "No se pudo enviar el mensaje. Por favor intenta más tarde o verifica la configuración SMTP.";
            }

            header("Location: ban.php?pagina=soporte");
            exit;
        }

        header("Location: ban.php?pagina=soporte");
        exit;
    }
}