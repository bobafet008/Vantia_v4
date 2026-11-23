<?php
class AuthController
{
    public function formulario()
    {
        $tipo = $_GET['tipo'] ?? '';
        vista('login', ['tipo' => $tipo]);
    }

    private function asegurarColumnaIntentos($conexion)
    {
        $tablas = ['usuarios_men', 'usuarios_may', 'empresas', 'emprendimientos'];
        foreach ($tablas as $t) {
            try {
                $col = $conexion->query("SHOW COLUMNS FROM $t LIKE 'intentos_fallidos'")->fetch();
                if (!$col) {
                    $conexion->exec("ALTER TABLE $t ADD COLUMN intentos_fallidos INT NOT NULL DEFAULT 0");
                }
            } catch (Exception $e) {}
        }
    }

    private function buscarCuentaPorCorreo($conexion, $correo)
    {
        
        try {
            $stmt = $conexion->prepare("SELECT id, nombre, correo, contrasena, baneado, baneado_hasta, motivo_baneo, intentos_fallidos, verificado FROM emprendimientos WHERE correo = ? LIMIT 1");
            $stmt->execute([$correo]);
            $cuenta = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($cuenta) {
                $cuenta['tabla'] = 'emprendimientos';
                return $cuenta;
            }
        } catch (Exception $e) {}

        
        try {
            $stmt = $conexion->prepare("SELECT id, nombre, correo, contrasena, baneado, baneado_hasta, motivo_baneo, intentos_fallidos, verificada, tipo_cuenta, representante FROM empresas WHERE correo = ? LIMIT 1");
            $stmt->execute([$correo]);
            $cuenta = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($cuenta) {
                $cuenta['tabla'] = 'empresas';
                return $cuenta;
            }
        } catch (Exception $e) {}

        
        try {
            $stmt = $conexion->prepare("SELECT id, nombre, correo, contrasena, tipo, baneado, baneado_hasta, motivo_baneo, intentos_fallidos FROM usuarios_may WHERE correo = ? LIMIT 1");
            $stmt->execute([$correo]);
            $cuenta = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($cuenta) {
                $cuenta['tabla'] = 'usuarios_may';
                $cuenta['es_menor'] = 0;
                return $cuenta;
            }
        } catch (Exception $e) {}

        
        try {
            $stmt = $conexion->prepare("SELECT id, nombre, correo, contrasena, 'empleado' AS tipo, baneado, baneado_hasta, motivo_baneo, intentos_fallidos FROM usuarios_men WHERE correo = ? LIMIT 1");
            $stmt->execute([$correo]);
            $cuenta = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($cuenta) {
                $cuenta['tabla'] = 'usuarios_men';
                $cuenta['es_menor'] = 1;
                return $cuenta;
            }
        } catch (Exception $e) {}

        return null;
    }

    public function validar()
    {
        require __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../Models/Usuario.php';
        require_once __DIR__ . '/../Models/Empresa.php';
        require_once __DIR__ . '/../Models/Emprendimiento.php';
        require_once __DIR__ . '/../Models/UsuarioAdmin.php';
        require_once __DIR__ . '/../Models/RegistroAdmin.php';

        $this->asegurarColumnaIntentos($conexion);

        $modeloAdmin = new UsuarioAdmin($conexion);

        $correo = trim($_POST['correo'] ?? '');
        $contrasena = $_POST['contrasena'] ?? '';
        $tipoRequerido = $_POST['tipo'] ?? '';

        if ($correo === '' || $contrasena === '') {
            vista('login', [
                'tipo' => $tipoRequerido,
                'error' => 'Por favor, ingresa tu correo y contraseña.',
                'correo' => $correo,
            ]);
            return;
        }

        
        $admin = $modeloAdmin->buscarPorCorreo($correo);
        if ($admin && password_verify($contrasena, $admin['contrasena'])) {
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = (int) $admin['id'];
            $_SESSION['usuario'] = $admin['nombre'];
            $_SESSION['correo'] = $admin['correo'];
            $_SESSION['tipo'] = 'admin';
            $_SESSION['cargo'] = $admin['cargo'] ?? 'Administrador General';
            $_SESSION['es_menor'] = 0;
            $_SESSION['tabla_usuario'] = 'usuarios_admin';

            header('Location: index.php?pagina=panel_admin');
            exit;
        }

        
        $cuenta = $this->buscarCuentaPorCorreo($conexion, $correo);

        if ($cuenta) {
            $tabla = $cuenta['tabla'];
            $id = (int) $cuenta['id'];

            
            if (estaBaneado($cuenta)) {
                vista('login', [
                    'tipo' => $tipoRequerido,
                    'error' => mensajeBaneo($cuenta),
                    'correo' => $correo,
                ]);
                return;
            }

            
            if (password_verify($contrasena, $cuenta['contrasena'])) {
                
                try {
                    $conexion->exec("UPDATE $tabla SET intentos_fallidos = 0 WHERE id = $id");
                    if ($tabla === 'emprendimientos') {
                        $conexion->exec("UPDATE empresas SET intentos_fallidos = 0 WHERE id = $id");
                    } elseif ($tabla === 'empresas') {
                        $conexion->exec("UPDATE emprendimientos SET intentos_fallidos = 0 WHERE id = $id");
                    }
                } catch (Exception $e) {}

                
                if ($tabla === 'emprendimientos' && isset($cuenta['verificado']) && (int) $cuenta['verificado'] === 0) {
                    vista('login', [
                        'tipo' => $tipoRequerido,
                        'error' => 'Tu emprendimiento aún está pendiente de revisión por el administrador.',
                        'correo' => $correo,
                    ]);
                    return;
                }

                if ($tabla === 'empresas' && isset($cuenta['verificada']) && (int) $cuenta['verificada'] === 0) {
                    $msgPendiente = (($cuenta['tipo_cuenta'] ?? '') === 'emprendimiento')
                        ? 'Tu emprendimiento aún está pendiente de revisión por el administrador.'
                        : 'Tu empresa aún está pendiente de revisión por el administrador.';
                    vista('login', [
                        'tipo' => $tipoRequerido,
                        'error' => $msgPendiente,
                        'correo' => $correo,
                    ]);
                    return;
                }

                session_regenerate_id(true);
                $_SESSION['usuario_id'] = $id;
                $_SESSION['usuario'] = $cuenta['nombre'];
                $_SESSION['correo'] = $cuenta['correo'];
                $_SESSION['tabla_usuario'] = $tabla;

                if ($tabla === 'emprendimientos') {
                    $_SESSION['tipo'] = 'empresa';
                    $_SESSION['subtipo'] = 'emprendimiento';
                    $_SESSION['es_menor'] = 0;
                    $_SESSION['representante'] = $cuenta['representante'] ?? null;
                    header('Location: index.php?pagina=panel_empresa');
                    exit;
                }

                if ($tabla === 'empresas') {
                    $_SESSION['tipo'] = 'empresa';
                    $_SESSION['subtipo'] = $cuenta['tipo_cuenta'] ?? 'empresa';
                    $_SESSION['es_menor'] = 0;
                    $_SESSION['representante'] = $cuenta['representante'] ?? null;
                    header('Location: index.php?pagina=panel_empresa');
                    exit;
                }

                
                $_SESSION['tipo'] = $cuenta['tipo'] ?? 'empleado';
                $_SESSION['es_menor'] = (int) ($cuenta['es_menor'] ?? 0);

                if (($cuenta['tipo'] ?? '') === 'admin') {
                    header('Location: index.php?pagina=panel_admin');
                } else {
                    header('Location: index.php?pagina=trabajos');
                }
                exit;
            } else {
                
                $nuevosIntentos = ((int) ($cuenta['intentos_fallidos'] ?? 0)) + 1;

                if ($nuevosIntentos >= 3) {
                    
                    $motivoBloqueo = 'Bloqueo automático por 3 intentos fallidos de contraseña';
                    try {
                        $stmtBan = $conexion->prepare("UPDATE $tabla SET baneado = 1, motivo_baneo = ?, intentos_fallidos = ? WHERE id = ?");
                        $stmtBan->execute([$motivoBloqueo, $nuevosIntentos, $id]);

                        if ($tabla === 'emprendimientos') {
                            $stmtSync = $conexion->prepare("UPDATE empresas SET baneado = 1, motivo_baneo = ?, intentos_fallidos = ? WHERE id = ?");
                            $stmtSync->execute([$motivoBloqueo, $nuevosIntentos, $id]);
                        } elseif ($tabla === 'empresas') {
                            $stmtSync = $conexion->prepare("UPDATE emprendimientos SET baneado = 1, motivo_baneo = ?, intentos_fallidos = ? WHERE id = ?");
                            $stmtSync->execute([$motivoBloqueo, $nuevosIntentos, $id]);
                        }
                    } catch (Exception $eBloqueo) {}

                    
                    try {
                        $regAdmin = new RegistroAdmin($conexion);
                        $relaciones = [];
                        if ($tabla === 'usuarios_may') $relaciones['usuario_may_id'] = $id;
                        elseif ($tabla === 'usuarios_men') $relaciones['usuario_men_id'] = $id;
                        elseif ($tabla === 'empresas') $relaciones['empresa_id'] = $id;
                        elseif ($tabla === 'emprendimientos') $relaciones['emprendimiento_id'] = $id;

                        $regAdmin->registrar(
                            null,
                            'bloqueo_correo',
                            'Bloqueo por Intentos Fallidos',
                            'Cuenta suspendida tras fallar 3 veces la contraseña',
                            $cuenta['correo'],
                            null,
                            $relaciones
                        );
                    } catch (Exception $eReg) {}

                    vista('login', [
                        'tipo' => $tipoRequerido,
                        'error' => 'Tu cuenta ha sido bloqueada tras fallar 3 veces la contraseña. Por favor, contacta a Atención al Cliente para reactivarla.',
                        'correo' => $correo,
                    ]);
                    return;
                } else {
                    
                    try {
                        $stmtInc = $conexion->prepare("UPDATE $tabla SET intentos_fallidos = ? WHERE id = ?");
                        $stmtInc->execute([$nuevosIntentos, $id]);

                        if ($tabla === 'emprendimientos') {
                            $stmtSync = $conexion->prepare("UPDATE empresas SET intentos_fallidos = ? WHERE id = ?");
                            $stmtSync->execute([$nuevosIntentos, $id]);
                        } elseif ($tabla === 'empresas') {
                            $stmtSync = $conexion->prepare("UPDATE emprendimientos SET intentos_fallidos = ? WHERE id = ?");
                            $stmtSync->execute([$nuevosIntentos, $id]);
                        }
                    } catch (Exception $eInc) {}

                    $restantes = 3 - $nuevosIntentos;
                    vista('login', [
                        'tipo' => $tipoRequerido,
                        'error' => 'Contraseña incorrecta. Te quedan ' . $restantes . ' intento(s) antes de que la cuenta sea bloqueada.',
                        'correo' => $correo,
                    ]);
                    return;
                }
            }
        }

        vista('login', [
            'tipo' => $tipoRequerido,
            'error' => 'Correo o contraseña incorrectos.',
            'correo' => $correo,
        ]);
    }

    public function salir()
    {
        $_SESSION = [];
        if (session_id() != '' || headers_sent()) {
            session_destroy();
        }
        header('Location: index.php?pagina=inicio');
        exit;
    }
}
