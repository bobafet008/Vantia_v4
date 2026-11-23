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
        // 1. Emprendimientos
        try {
            $stmt = $conexion->prepare("SELECT id, nombre, correo, contrasena, baneado, baneado_hasta, motivo_baneo, intentos_fallidos, verificado FROM emprendimientos WHERE correo = ? LIMIT 1");
            $stmt->execute([$correo]);
            $cuenta = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($cuenta) {
                $cuenta['tabla'] = 'emprendimientos';
                return $cuenta;
            }
        } catch (Exception $e) {}

        // 2. Empresas
        try {
            $stmt = $conexion->prepare("SELECT id, nombre, correo, contrasena, baneado, baneado_hasta, motivo_baneo, intentos_fallidos, verificada, tipo_cuenta, representante FROM empresas WHERE correo = ? LIMIT 1");
            $stmt->execute([$correo]);
            $cuenta = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($cuenta) {
                $cuenta['tabla'] = 'empresas';
                return $cuenta;
            }
        } catch (Exception $e) {}

        // 3. Usuarios Mayores
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

        // 4. Usuarios Menores
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

        // 1. Verificar si las credenciales corresponden a un Administrador en usuarios_admin
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

            header('Location: ban.php?pagina=panel_admin');
            exit;
        }

        // 2. Buscar cuenta de usuario en las tablas del sistema
        $cuenta = $this->buscarCuentaPorCorreo($conexion, $correo);

        if ($cuenta) {
            $tabla = $cuenta['tabla'];
            $id = (int) $cuenta['id'];

            // Si la cuenta ya se encuentra suspendida o bloqueada
            if (estaBaneado($cuenta)) {
                vista('login', [
                    'tipo' => $tipoRequerido,
                    'error' => mensajeBaneo($cuenta),
                    'correo' => $correo,
                ]);
                return;
            }

            // Validar contraseña
            if (password_verify($contrasena, $cuenta['contrasena'])) {
                // Restablecer contador de intentos fallidos al iniciar sesión con éxito
                try {
                    $conexion->exec("UPDATE $tabla SET intentos_fallidos = 0 WHERE id = $id");
                    if ($tabla === 'emprendimientos') {
                        $conexion->exec("UPDATE empresas SET intentos_fallidos = 0 WHERE id = $id");
                    } elseif ($tabla === 'empresas') {
                        $conexion->exec("UPDATE emprendimientos SET intentos_fallidos = 0 WHERE id = $id");
                    }
                } catch (Exception $e) {}

                // Validar si requiere verificación previa por el administrador
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
                    header('Location: ban.php?pagina=panel_empresa');
                    exit;
                }

                if ($tabla === 'empresas') {
                    $_SESSION['tipo'] = 'empresa';
                    $_SESSION['subtipo'] = $cuenta['tipo_cuenta'] ?? 'empresa';
                    $_SESSION['es_menor'] = 0;
                    $_SESSION['representante'] = $cuenta['representante'] ?? null;
                    header('Location: ban.php?pagina=panel_empresa');
                    exit;
                }

                // usuarios_may y usuarios_men
                $_SESSION['tipo'] = $cuenta['tipo'] ?? 'empleado';
                $_SESSION['es_menor'] = (int) ($cuenta['es_menor'] ?? 0);

                if (($cuenta['tipo'] ?? '') === 'admin') {
                    header('Location: ban.php?pagina=panel_admin');
                } else {
                    header('Location: ban.php?pagina=trabajos');
                }
                exit;
            } else {
                // Contraseña incorrecta: incrementar intentos fallidos
                $nuevosIntentos = ((int) ($cuenta['intentos_fallidos'] ?? 0)) + 1;

                if ($nuevosIntentos >= 3) {
                    // Bloqueo tras fallar 3 veces la contraseña
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

                    // Registrar evento en registros_admin con llaves foráneas conectadas
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
                    // Actualizar contador y notificar intentos restantes
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
        header('Location: ban.php?pagina=inicio');
        exit;
    }
}
