<?php
class AuthController
{
    public function formulario()
    {
        $tipo = $_GET['tipo'] ?? '';
        vista('login', ['tipo' => $tipo]);
    }

    public function validar()
    {
        require __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../Models/Usuario.php';
        require_once __DIR__ . '/../Models/Empresa.php';
        require_once __DIR__ . '/../Models/Emprendimiento.php';
        require_once __DIR__ . '/../Models/UsuarioAdmin.php';
        require_once __DIR__ . '/../Models/BloqueoCorreo.php';

        $modeloUsuario        = new Usuario($conexion);
        $modeloEmpresa        = new Empresa($conexion);
        $modeloEmprendimiento = new Emprendimiento($conexion);
        $modeloAdmin          = new UsuarioAdmin($conexion);
        $modeloBloqueo        = new BloqueoCorreo($conexion);

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

        // Verificar si el correo está bloqueado por superar el límite de intentos fallidos
        if ($modeloBloqueo->estaBloqueado($correo)) {
            vista('login', [
                'tipo' => $tipoRequerido,
                'error' => 'Esta cuenta de correo ha sido bloqueada por superar el límite de 3 intentos fallidos de inicio de sesión. Por favor, contacta al administrador para que desbloquee tu acceso.',
                'correo' => $correo,
            ]);
            return;
        }

        // 1. Verificar si las credenciales corresponden a un Administrador en usuarios_admin
        $admin = $modeloAdmin->buscarPorCorreo($correo);
        if ($admin && password_verify($contrasena, $admin['contrasena'])) {
            $modeloBloqueo->resetear($correo);
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

        // 2. Si las credenciales corresponden a un Emprendimiento en la tabla emprendimientos
        $emprendimiento = $modeloEmprendimiento->buscarPorCorreo($correo);
        if ($emprendimiento && password_verify($contrasena, $emprendimiento['contrasena'])) {
            if (estaBaneado($emprendimiento)) {
                vista('login', [
                    'tipo' => $tipoRequerido,
                    'error' => mensajeBaneo($emprendimiento),
                    'correo' => $correo,
                ]);
                return;
            }

            if (isset($emprendimiento['verificado']) && (int) $emprendimiento['verificado'] === 0) {
                vista('login', [
                    'tipo' => $tipoRequerido,
                    'error' => 'Tu emprendimiento aún está pendiente de revisión por el administrador.',
                    'correo' => $correo,
                ]);
                return;
            }

            $modeloBloqueo->resetear($correo);
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = (int) $emprendimiento['id'];
            $_SESSION['usuario'] = $emprendimiento['nombre'];
            $_SESSION['representante'] = $emprendimiento['representante'] ?? null;
            $_SESSION['correo'] = $emprendimiento['correo'];
            $_SESSION['tipo'] = 'empresa';
            $_SESSION['subtipo'] = 'emprendimiento';
            $_SESSION['es_menor'] = 0;
            $_SESSION['tabla_usuario'] = 'emprendimientos';

            header('Location: ban.php?pagina=panel_empresa');
            exit;
        }

        // 3. Si se especificó tipo empresa o emprendimiento, intentar validar contra la tabla empresas
        if ($tipoRequerido === 'empresa' || $tipoRequerido === 'emprendimiento') {
            $empresa = $modeloEmpresa->buscarPorCorreo($correo);
            if ($empresa && password_verify($contrasena, $empresa['contrasena'])) {
                if (estaBaneado($empresa)) {
                    vista('login', [
                        'tipo' => $tipoRequerido,
                        'error' => mensajeBaneo($empresa),
                        'correo' => $correo,
                    ]);
                    return;
                }

                if (isset($empresa['verificada']) && (int) $empresa['verificada'] === 0) {
                    $msgPendiente = (($empresa['tipo_cuenta'] ?? '') === 'emprendimiento')
                        ? 'Tu emprendimiento aún está pendiente de revisión por el administrador.'
                        : 'Tu empresa aún está pendiente de revisión por el administrador.';
                    vista('login', [
                        'tipo' => $tipoRequerido,
                        'error' => $msgPendiente,
                        'correo' => $correo,
                    ]);
                    return;
                }

                $modeloBloqueo->resetear($correo);
                session_regenerate_id(true);
                $_SESSION['usuario_id'] = (int) $empresa['id'];
                $_SESSION['usuario'] = $empresa['nombre'];
                $_SESSION['representante'] = $empresa['representante'] ?? null;
                $_SESSION['correo'] = $empresa['correo'];
                $_SESSION['tipo'] = 'empresa';
                $_SESSION['subtipo'] = $empresa['tipo_cuenta'] ?? 'empresa';
                $_SESSION['es_menor'] = 0;
                $_SESSION['tabla_usuario'] = 'empresas';

                header('Location: ban.php?pagina=panel_empresa');
                exit;
            }
        }

        // 4. Buscar en usuarios mayores (usuarios_may) o menores (usuarios_men)
        $usuario = $modeloUsuario->buscarPorCorreo($correo);
        if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
            if (estaBaneado($usuario)) {
                vista('login', [
                    'tipo' => $tipoRequerido,
                    'error' => mensajeBaneo($usuario),
                    'correo' => $correo,
                ]);
                return;
            }

            $modeloBloqueo->resetear($correo);
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = (int) $usuario['id'];
            $_SESSION['usuario'] = $usuario['nombre'];
            $_SESSION['correo'] = $usuario['correo'];
            $_SESSION['tipo'] = $usuario['tipo'];
            $_SESSION['es_menor'] = (int) ($usuario['es_menor'] ?? 0);
            $_SESSION['tabla_usuario'] = $usuario['tabla_origen'] ?? ($usuario['es_menor'] ? 'usuarios_men' : 'usuarios_may');

            if ($usuario['tipo'] === 'admin') {
                header('Location: ban.php?pagina=panel_admin');
            } else {
                header('Location: ban.php?pagina=trabajos');
            }
            exit;
        }

        // 5. Si no se especificó tipo empresa/emprendimiento pero las credenciales corresponden a una empresa/emprendimiento
        if ($tipoRequerido !== 'empresa' && $tipoRequerido !== 'emprendimiento') {
            $empresa = $modeloEmpresa->buscarPorCorreo($correo);
            if ($empresa && password_verify($contrasena, $empresa['contrasena'])) {
                if (estaBaneado($empresa)) {
                    vista('login', [
                        'tipo' => $tipoRequerido,
                        'error' => mensajeBaneo($empresa),
                        'correo' => $correo,
                    ]);
                    return;
                }

                if (isset($empresa['verificada']) && (int) $empresa['verificada'] === 0) {
                    $msgPendiente = (($empresa['tipo_cuenta'] ?? '') === 'emprendimiento')
                        ? 'Tu emprendimiento aún está pendiente de revisión por el administrador.'
                        : 'Tu empresa aún está pendiente de revisión por el administrador.';
                    vista('login', [
                        'tipo' => $tipoRequerido,
                        'error' => $msgPendiente,
                        'correo' => $correo,
                    ]);
                    return;
                }

                $modeloBloqueo->resetear($correo);
                session_regenerate_id(true);
                $_SESSION['usuario_id'] = (int) $empresa['id'];
                $_SESSION['usuario'] = $empresa['nombre'];
                $_SESSION['representante'] = $empresa['representante'] ?? null;
                $_SESSION['correo'] = $empresa['correo'];
                $_SESSION['tipo'] = 'empresa';
                $_SESSION['subtipo'] = $empresa['tipo_cuenta'] ?? 'empresa';
                $_SESSION['es_menor'] = 0;
                $_SESSION['tabla_usuario'] = 'empresas';

                header('Location: ban.php?pagina=panel_empresa');
                exit;
            }
        }

        // Registrar intento fallido para el correo ingresado (máximo 3 intentos)
        $fallo = $modeloBloqueo->registrarFallo($correo);
        if ($fallo['bloqueado']) {
            $errorLogin = 'Has superado el límite de 3 intentos fallidos. Tu cuenta ha sido bloqueada por seguridad. Contacta al administrador para su desbloqueo.';
        } else {
            $restantes = $fallo['restantes'];
            $errorLogin = 'Correo o contraseña incorrectos. Te quedan ' . $restantes . ' intento(s) antes de que la cuenta sea bloqueada.';
        }

        vista('login', [
            'tipo' => $tipoRequerido,
            'error' => $errorLogin,
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
