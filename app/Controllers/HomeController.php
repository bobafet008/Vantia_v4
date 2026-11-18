<?php
class HomeController
{
    public function inicio()
    {
        vista('inicio');
    }

    public function empleado()
    {
        vista('empleado');
    }

    public function empresa()
    {
        vista('empresa');
    }

    public function trabajos()
    {
        require __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../Models/Postulacion.php';

        $empleos = $conexion->query(
            "SELECT e.*, emp.nombre AS empresa, emp.tipo_cuenta, emp.nit, emp.roe, emp.verificada
             FROM empleos e
             INNER JOIN empresas emp ON emp.id = e.empresa_id
             WHERE e.activo = 1
             ORDER BY e.creado_en DESC"
        )->fetchAll();

        $postuladosIds = [];
        if (!empty($_SESSION['usuario_id']) && ($_SESSION['tipo'] ?? '') === 'empleado') {
            $modeloPostulacion = new Postulacion($conexion);
            $candidatoTipo = !empty($_SESSION['es_menor']) ? 'menor' : 'mayor';
            $postuladosIds = $modeloPostulacion->obtenerIdsPostuladosPorCandidato((int) $_SESSION['usuario_id'], $candidatoTipo);
        }

        vista('trabajos', [
            'empleos' => $empleos,
            'postuladosIds' => $postuladosIds,
            'mensajeExito' => $_SESSION['flash_exito'] ?? '',
            'mensajeError' => $_SESSION['flash_error'] ?? '',
        ]);
        unset($_SESSION['flash_exito'], $_SESSION['flash_error']);
    }

    public function postular()
    {
        if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo'] ?? '') !== 'empleado') {
            $_SESSION['flash_error'] = 'Debes iniciar sesión como empleado para poder postularte a las vacantes.';
            header('Location: ban.php?pagina=login&tipo=empleado');
            exit;
        }

        $empleoId = (int) ($_POST['empleo_id'] ?? 0);
        $mensaje = trim($_POST['mensaje'] ?? '');
        $candidatoId = (int) $_SESSION['usuario_id'];
        $candidatoTipo = !empty($_SESSION['es_menor']) ? 'menor' : 'mayor';

        if ($empleoId <= 0) {
            $_SESSION['flash_error'] = 'Vacante no válida.';
            header('Location: ban.php?pagina=trabajos');
            exit;
        }

        require __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../Models/Postulacion.php';
        $modelo = new Postulacion($conexion);

        if ($modelo->yaPostulado($empleoId, $candidatoId, $candidatoTipo)) {
            $_SESSION['flash_error'] = 'Ya te has postulado previamente a esta vacante.';
        } else {
            $modelo->crear($empleoId, $candidatoId, $candidatoTipo, $mensaje ?: null);
            $_SESSION['flash_exito'] = '¡Tu postulación ha sido enviada con éxito! La empresa revisará tu perfil.';
        }

        header('Location: ban.php?pagina=trabajos');
        exit;
    }

    public function misPostulaciones()
    {
        if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo'] ?? '') !== 'empleado') {
            accesoDenegado('El apartado de postulaciones es de uso exclusivo para jóvenes y candidatos postulantes.', 'Mis Postulaciones');
        }

        require __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../Models/Postulacion.php';
        require_once __DIR__ . '/../Models/Calificacion.php';

        $candidatoId = (int) $_SESSION['usuario_id'];
        $candidatoTipo = !empty($_SESSION['es_menor']) ? 'menor' : 'mayor';

        $modelo = new Postulacion($conexion);
        $postulaciones = $modelo->obtenerPorCandidato($candidatoId, $candidatoTipo);

        $modeloCalif = new Calificacion($conexion);
        $calificaciones = $modeloCalif->obtenerPorCandidato($candidatoId, $candidatoTipo);

        vista('mis_postulaciones', [
            'postulaciones'  => $postulaciones,
            'calificaciones' => $calificaciones,
            'mensajeExito'   => $_SESSION['flash_exito'] ?? '',
            'mensajeError'   => $_SESSION['flash_error'] ?? '',
        ]);
        unset($_SESSION['flash_exito'], $_SESSION['flash_error']);
    }

    public function guardarCalificacion()
    {
        if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo'] ?? '') !== 'empleado') {
            accesoDenegado('Solo los empleados y postulantes pueden calificar a las empresas donde trabajan.', 'Calificaciones de Empresa');
        }

        $candidatoId = (int) $_SESSION['usuario_id'];
        $candidatoTipo = !empty($_SESSION['es_menor']) ? 'menor' : 'mayor';
        $empresaId = (int) ($_POST['empresa_id'] ?? 0);
        $puntuacion = isset($_POST['puntuacion']) ? (int) $_POST['puntuacion'] : -1;
        $comentario = trim($_POST['comentario'] ?? '');

        if ($empresaId <= 0 || $puntuacion < 0 || $puntuacion > 5) {
            $_SESSION['flash_error'] = 'Por favor selecciona una puntuación válida entre 0 y 5.';
            header('Location: ban.php?pagina=mis_postulaciones#calificaciones');
            exit;
        }

        require __DIR__ . '/../../config/database.php';

        // Verificar que el empleado realmente trabaja o fue contratado por esta empresa
        $stmt = $conexion->prepare(
            "SELECT COUNT(*) FROM postulaciones p 
             INNER JOIN empleos e ON e.id = p.empleo_id 
             WHERE p.candidato_id = ? AND p.candidato_tipo = ? AND e.empresa_id = ? AND p.estado = 'contratado'"
        );
        $stmt->execute([$candidatoId, $candidatoTipo, $empresaId]);
        $trabajaAhi = (int) $stmt->fetchColumn() > 0;

        if (!$trabajaAhi) {
            $_SESSION['flash_error'] = 'Solo puedes calificar a empresas donde has sido contratado para trabajar.';
            header('Location: ban.php?pagina=mis_postulaciones');
            exit;
        }

        require_once __DIR__ . '/../Models/Calificacion.php';
        $modeloCalif = new Calificacion($conexion);
        $modeloCalif->guardar($empresaId, $candidatoId, $candidatoTipo, $puntuacion, $comentario ?: null);

        $_SESSION['flash_exito'] = '¡Tu calificación de ' . $puntuacion . '/5 para la empresa fue registrada exitosamente!';
        header('Location: ban.php?pagina=mis_postulaciones#calificaciones');
        exit;
    }

    public function cancelarPostulacion()
    {
        if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo'] ?? '') !== 'empleado') {
            accesoDenegado('Acción permitida únicamente para candidatos postulantes.', 'Mis Postulaciones');
        }

        $postulacionId = (int) ($_POST['postulacion_id'] ?? 0);
        $candidatoId = (int) $_SESSION['usuario_id'];
        $candidatoTipo = !empty($_SESSION['es_menor']) ? 'menor' : 'mayor';

        if ($postulacionId > 0) {
            require __DIR__ . '/../../config/database.php';
            $stmt = $conexion->prepare("DELETE FROM postulaciones WHERE id = ? AND candidato_id = ? AND candidato_tipo = ? AND estado = 'pendiente'");
            $stmt->execute([$postulacionId, $candidatoId, $candidatoTipo]);

            if ($stmt->rowCount() > 0) {
                $_SESSION['flash_exito'] = 'Has retirado tu postulación correctamente.';
            } else {
                $_SESSION['flash_error'] = 'No fue posible retirar la postulación o ya está en proceso de revisión.';
            }
        }

        header('Location: ban.php?pagina=mis_postulaciones');
        exit;
    }

    public function panelEmpresa()
    {
        if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo'] ?? '') !== 'empresa') {
            accesoDenegado('El Panel de Empresa es de acceso exclusivo para organizaciones y empresas verificadas. No puedes ingresar desde una cuenta de candidato o visitante.', 'Panel de Empresa');
        }

        require __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../Models/Postulacion.php';

        $empresaId = (int) $_SESSION['usuario_id'];

        // Obtener vacantes de la empresa
        $consulta = $conexion->prepare('SELECT * FROM empleos WHERE empresa_id = ? ORDER BY creado_en DESC');
        $consulta->execute([$empresaId]);
        $empleos = $consulta->fetchAll();

        // Obtener postulantes a las vacantes de la empresa
        $modeloPostulacion = new Postulacion($conexion);
        $postulaciones = $modeloPostulacion->obtenerPorEmpresa($empresaId);

        // Obtener calificaciones recibidas por la empresa
        require_once __DIR__ . '/../Models/Calificacion.php';
        $modeloCalif = new Calificacion($conexion);
        $calificacionesRecibidas = $modeloCalif->obtenerPorEmpresa($empresaId);
        $resumenCalif = $modeloCalif->obtenerPromedioPorEmpresa($empresaId);

        // Obtener datos de la empresa actual
        $stmtEmp = $conexion->prepare('SELECT * FROM empresas WHERE id = ? LIMIT 1');
        $stmtEmp->execute([$empresaId]);
        $empresa = $stmtEmp->fetch();

        vista('panel_empresa', [
            'empresa' => $empresa,
            'empleos' => $empleos,
            'postulaciones' => $postulaciones,
            'calificacionesRecibidas' => $calificacionesRecibidas,
            'resumenCalif' => $resumenCalif,
            'exito' => $_SESSION['empleo_exito'] ?? '',
            'error' => $_SESSION['empleo_error'] ?? '',
        ]);
        unset($_SESSION['empleo_exito'], $_SESSION['empleo_error']);
    }

    public function guardarEmpleo()
    {
        if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo'] ?? '') !== 'empresa') {
            accesoDenegado('Solo las empresas registradas pueden publicar nuevas vacantes.', 'Publicar Empleo');
        }

        require __DIR__ . '/../../config/database.php';

        $titulo = trim($_POST['titulo'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $tipo = $_POST['tipo_empleo'] ?? '';
        $descripcion = trim($_POST['descripcion'] ?? '');
        $requisitos = trim($_POST['requisitos'] ?? '');
        $salario = trim($_POST['salario'] ?? '');
        $ubicacion = trim($_POST['ubicacion'] ?? '');
        $tiposValidos = ['medio_tiempo', 'practicas', 'fines_semana', 'remoto'];

        if ($titulo === '' || $categoria === '' || $descripcion === '' || !in_array($tipo, $tiposValidos, true)) {
            $_SESSION['empleo_error'] = 'Completa los campos obligatorios y selecciona un tipo de empleo válido.';
        } else {
            $consulta = $conexion->prepare(
                'INSERT INTO empleos (empresa_id, titulo, categoria, tipo, descripcion, requisitos, salario, ubicacion)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $consulta->execute([
                (int) $_SESSION['usuario_id'],
                $titulo,
                $categoria,
                $tipo,
                $descripcion,
                $requisitos ?: null,
                $salario ?: null,
                $ubicacion ?: null,
            ]);
            $_SESSION['empleo_exito'] = 'La vacante se publicó correctamente.';
        }

        header('Location: ban.php?pagina=panel_empresa');
        exit;
    }

    public function actualizarEmpleo()
    {
        if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo'] ?? '') !== 'empresa') {
            accesoDenegado('Solo las empresas registradas pueden modificar vacantes.', 'Editar Vacante');
        }

        require __DIR__ . '/../../config/database.php';

        $empleoId = (int) ($_POST['empleo_id'] ?? 0);
        $empresaId = (int) $_SESSION['usuario_id'];
        $titulo = trim($_POST['titulo'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $tipo = $_POST['tipo_empleo'] ?? '';
        $descripcion = trim($_POST['descripcion'] ?? '');
        $requisitos = trim($_POST['requisitos'] ?? '');
        $salario = trim($_POST['salario'] ?? '');
        $ubicacion = trim($_POST['ubicacion'] ?? '');
        $tiposValidos = ['medio_tiempo', 'practicas', 'fines_semana', 'remoto'];

        if ($empleoId <= 0) {
            $_SESSION['empleo_error'] = 'Identificador de vacante no válido.';
        } elseif ($titulo === '' || $categoria === '' || $descripcion === '' || !in_array($tipo, $tiposValidos, true)) {
            $_SESSION['empleo_error'] = 'Por favor completa el título, categoría, tipo y descripción de la vacante.';
        } else {
            $stmt = $conexion->prepare(
                'UPDATE empleos 
                 SET titulo = ?, categoria = ?, tipo = ?, descripcion = ?, requisitos = ?, salario = ?, ubicacion = ? 
                 WHERE id = ? AND empresa_id = ?'
            );
            $stmt->execute([
                $titulo,
                $categoria,
                $tipo,
                $descripcion,
                $requisitos ?: null,
                $salario ?: null,
                $ubicacion ?: null,
                $empleoId,
                $empresaId
            ]);
            $_SESSION['empleo_exito'] = '¡La vacante ha sido actualizada exitosamente!';
        }

        header('Location: ban.php?pagina=panel_empresa#mis-vacantes');
        exit;
    }

    public function alternarEmpleo()
    {
        if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo'] ?? '') !== 'empresa') {
            accesoDenegado('Solo la empresa propietaria de la vacante puede modificar su estado.', 'Gestión de Vacantes');
        }

        $empleoId = (int) ($_POST['empleo_id'] ?? 0);
        $empresaId = (int) $_SESSION['usuario_id'];

        if ($empleoId > 0) {
            require __DIR__ . '/../../config/database.php';
            $stmt = $conexion->prepare('UPDATE empleos SET activo = 1 - activo WHERE id = ? AND empresa_id = ?');
            $stmt->execute([$empleoId, $empresaId]);
            $_SESSION['empleo_exito'] = 'El estado de la vacante ha sido actualizado.';
        }

        header('Location: ban.php?pagina=panel_empresa');
        exit;
    }

    public function eliminarEmpleo()
    {
        if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo'] ?? '') !== 'empresa') {
            accesoDenegado('Solo la empresa propietaria puede eliminar esta vacante.', 'Gestión de Vacantes');
        }

        $empleoId = (int) ($_POST['empleo_id'] ?? 0);
        $empresaId = (int) $_SESSION['usuario_id'];

        if ($empleoId > 0) {
            require __DIR__ . '/../../config/database.php';
            $stmt = $conexion->prepare('DELETE FROM empleos WHERE id = ? AND empresa_id = ?');
            $stmt->execute([$empleoId, $empresaId]);
            $_SESSION['empleo_exito'] = 'La vacante ha sido eliminada con éxito.';
        }

        header('Location: ban.php?pagina=panel_empresa');
        exit;
    }

    public function cambiarEstadoPostulacion()
    {
        if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo'] ?? '') !== 'empresa') {
            accesoDenegado('Solo las empresas pueden modificar el estado de sus postulantes.', 'Gestión de Postulantes');
        }

        $postulacionId = (int) ($_POST['postulacion_id'] ?? 0);
        $nuevoEstado = $_POST['estado'] ?? '';

        if ($postulacionId > 0 && $nuevoEstado !== '') {
            require __DIR__ . '/../../config/database.php';
            require_once __DIR__ . '/../Models/Postulacion.php';
            $modelo = new Postulacion($conexion);
            $modelo->cambiarEstado($postulacionId, $nuevoEstado);
            $_SESSION['empleo_exito'] = 'El estado del postulante ha sido actualizado.';
        }

        header('Location: ban.php?pagina=panel_empresa#postulantes');
        exit;
    }

    public function panelAdmin()
    {
        if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo'] ?? '') !== 'admin') {
            accesoDenegado('Este panel es de uso exclusivo para Administradores de la plataforma Vantia. Tu cuenta actual no cuenta con privilegios administrativos.', 'Panel de Administración');
        }

        require __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../Models/UsuarioMay.php';
        require_once __DIR__ . '/../Models/UsuarioMen.php';
        require_once __DIR__ . '/../Models/Empresa.php';
        require_once __DIR__ . '/../Models/Emprendimiento.php';
        require_once __DIR__ . '/../Models/Tutor.php';
        require_once __DIR__ . '/../Models/UsuarioAdmin.php';
        require_once __DIR__ . '/../Models/BloqueoCorreo.php';

        $modeloMay = new UsuarioMay($conexion);
        $modeloMen = new UsuarioMen($conexion);
        $modeloEmpresa = new Empresa($conexion);
        $modeloEmprendimiento = new Emprendimiento($conexion);
        $modeloTutor = new Tutor($conexion);
        $modeloAdmin = new UsuarioAdmin($conexion);
        $modeloBloqueo = new BloqueoCorreo($conexion);

        $usuariosMay = $modeloMay->obtenerTodos();
        $usuariosMen = $modeloMen->obtenerTodos();
        $tutores = $modeloTutor->obtenerTodos();
        $empresas = $modeloEmpresa->obtenerTodas();
        $emprendimientos = $modeloEmprendimiento->obtenerTodos();
        $administradores = $modeloAdmin->obtenerTodos();
        $bloqueosCorreo = $modeloBloqueo->obtenerTodos();
        $totalCorreosBloqueados = $modeloBloqueo->contarBloqueados();
        $mapaBloqueados = $modeloBloqueo->obtenerMapaBloqueados();

        $totalEmpleos = (int) $conexion->query('SELECT COUNT(*) FROM empleos')->fetchColumn();
        $totalPostulaciones = (int) $conexion->query('SELECT COUNT(*) FROM postulaciones')->fetchColumn();
        $totalEmpresas = $modeloEmpresa->contar();
        $totalEmprendimientos = $modeloEmprendimiento->contar();
        $totalMenores = $modeloMen->contar();
        $totalTutores = $modeloTutor->contar();
        $totalMayores = $modeloMay->contarCandidatos();
        $totalAdmins = $modeloAdmin->contar();
        $totalCandidatos = $totalMenores + $totalMayores;

        vista('panel_admin', [
            'usuariosMay' => $usuariosMay,
            'usuariosMen' => $usuariosMen,
            'tutores' => $tutores,
            'empresas' => $empresas,
            'emprendimientos' => $emprendimientos,
            'administradores' => $administradores,
            'bloqueosCorreo' => $bloqueosCorreo,
            'totalCorreosBloqueados' => $totalCorreosBloqueados,
            'mapaBloqueados' => $mapaBloqueados,
            'totalAdmins' => $totalAdmins,
            'totalEmpleos' => $totalEmpleos,
            'totalPostulaciones' => $totalPostulaciones,
            'totalEmpresas' => $totalEmpresas,
            'totalEmprendimientos' => $totalEmprendimientos,
            'totalMenores' => $totalMenores,
            'totalTutores' => $totalTutores,
            'totalMayores' => $totalMayores,
            'totalCandidatos' => $totalCandidatos,
            'exito' => $_SESSION['admin_exito'] ?? '',
            'error' => $_SESSION['admin_error'] ?? '',
        ]);
        unset($_SESSION['admin_exito'], $_SESSION['admin_error']);
    }

    public function toggleVerificarEmpresa()
    {
        if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo'] ?? '') !== 'admin') {
            accesoDenegado('Acción reservada exclusivamente para el Administrador del sistema.', 'Panel de Administración');
        }

        $empresaId = (int) ($_POST['empresa_id'] ?? $_POST['usuario_id'] ?? 0);

        if ($empresaId > 0) {
            require __DIR__ . '/../../config/database.php';
            require_once __DIR__ . '/../Models/Empresa.php';
            $modelo = new Empresa($conexion);
            $modelo->toggleVerificacion($empresaId);
            $_SESSION['admin_exito'] = 'El estado de verificación de la empresa ha sido actualizado.';
        }

        header('Location: ban.php?pagina=panel_admin');
        exit;
    }

    public function toggleVerificarEmprendimiento()
    {
        if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo'] ?? '') !== 'admin') {
            accesoDenegado('Acción reservada exclusivamente para el Administrador del sistema.', 'Panel de Administración');
        }

        $id = (int) ($_POST['emprendimiento_id'] ?? $_POST['usuario_id'] ?? 0);

        if ($id > 0) {
            require __DIR__ . '/../../config/database.php';
            require_once __DIR__ . '/../Models/Emprendimiento.php';
            require_once __DIR__ . '/../Models/Empresa.php';
            $modEmp = new Emprendimiento($conexion);
            $modEmp->toggleVerificacion($id);
            $modEmpresa = new Empresa($conexion);
            $modEmpresa->toggleVerificacion($id);
            $_SESSION['admin_exito'] = 'El estado de verificación del emprendimiento ha sido actualizado.';
        }

        header('Location: ban.php?pagina=panel_admin');
        exit;
    }

    public function eliminarEmprendimiento()
    {
        if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo'] ?? '') !== 'admin') {
            accesoDenegado('Acción reservada exclusivamente para el Administrador del sistema.', 'Panel de Administración');
        }

        $id = (int) ($_POST['emprendimiento_id'] ?? 0);

        if ($id > 0) {
            require __DIR__ . '/../../config/database.php';
            require_once __DIR__ . '/../Models/Emprendimiento.php';
            require_once __DIR__ . '/../Models/Empresa.php';
            $modEmp = new Emprendimiento($conexion);
            $modEmp->eliminar($id);
            $modEmpresa = new Empresa($conexion);
            $modEmpresa->eliminar($id);
            $_SESSION['admin_exito'] = 'El emprendimiento y sus vacantes asociadas han sido eliminados.';
        }

        header('Location: ban.php?pagina=panel_admin');
        exit;
    }

    public function eliminarEmpresa()
    {
        if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo'] ?? '') !== 'admin') {
            accesoDenegado('Acción reservada exclusivamente para el Administrador del sistema.', 'Panel de Administración');
        }

        $empresaId = (int) ($_POST['empresa_id'] ?? 0);

        if ($empresaId > 0) {
            require __DIR__ . '/../../config/database.php';
            require_once __DIR__ . '/../Models/Empresa.php';
            require_once __DIR__ . '/../Models/Emprendimiento.php';
            $modelo = new Empresa($conexion);
            $modelo->eliminar($empresaId);
            $modEmp = new Emprendimiento($conexion);
            $modEmp->eliminar($empresaId);
            $_SESSION['admin_exito'] = 'La empresa o emprendimiento y sus ofertas asociadas han sido eliminadas.';
        }

        header('Location: ban.php?pagina=panel_admin');
        exit;
    }

    public function eliminarUsuario()
    {
        if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo'] ?? '') !== 'admin') {
            accesoDenegado('Acción reservada exclusivamente para el Administrador del sistema.', 'Panel de Administración');
        }

        $usuarioId = (int) ($_POST['usuario_id'] ?? 0);
        $tabla = $_POST['tabla'] ?? 'usuarios_may';
        $miId = (int) $_SESSION['usuario_id'];

        if ($usuarioId > 0) {
            require __DIR__ . '/../../config/database.php';
            if ($tabla === 'usuarios_men') {
                require_once __DIR__ . '/../Models/UsuarioMen.php';
                $modelo = new UsuarioMen($conexion);
                $modelo->eliminar($usuarioId);
                $_SESSION['admin_exito'] = 'El joven menor de edad ha sido eliminado del sistema.';
            } else {
                if ($usuarioId === $miId) {
                    $_SESSION['admin_error'] = 'No puedes eliminar tu propia cuenta de administrador.';
                } else {
                    require_once __DIR__ . '/../Models/UsuarioMay.php';
                    $modelo = new UsuarioMay($conexion);
                    $modelo->eliminar($usuarioId);
                    $_SESSION['admin_exito'] = 'El usuario ha sido eliminado correctamente del sistema.';
                }
            }
        }

        header('Location: ban.php?pagina=panel_admin');
        exit;
    }

    public function banearCuenta()
    {
        if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo'] ?? '') !== 'admin') {
            accesoDenegado('Acción reservada exclusivamente para el Administrador del sistema.', 'Panel de Administración');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $tabla = $_POST['tabla'] ?? '';
        $tipoBaneo = $_POST['tipo_baneo'] ?? 'permanente';
        $duracion = (int) ($_POST['duracion_dias'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? 'Incumplimiento de las normas de la comunidad');

        $tablasValidas = ['empresas', 'emprendimientos', 'usuarios_may', 'usuarios_men'];
        if ($id <= 0 || !in_array($tabla, $tablasValidas, true)) {
            $_SESSION['admin_error'] = 'Identificador o tipo de entidad inválido.';
            header('Location: ban.php?pagina=panel_admin');
            exit;
        }

        if ($tabla === 'usuarios_may' && $id === (int) $_SESSION['usuario_id']) {
            $_SESSION['admin_error'] = 'No puedes suspender tu propia cuenta de administrador.';
            header('Location: ban.php?pagina=panel_admin');
            exit;
        }

        require __DIR__ . '/../../config/database.php';

        $baneadoHasta = null;
        if ($tipoBaneo === 'temporal' && $duracion > 0) {
            $fecha = new DateTime();
            $fecha->modify("+$duracion days");
            $baneadoHasta = $fecha->format('Y-m-d H:i:s');
        }

        $sql = "UPDATE $tabla SET baneado = 1, baneado_hasta = ?, motivo_baneo = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$baneadoHasta, $motivo ?: null, $id]);

        if ($tabla === 'emprendimientos') {
            $sync = $conexion->prepare("UPDATE empresas SET baneado = 1, baneado_hasta = ?, motivo_baneo = ? WHERE id = ?");
            $sync->execute([$baneadoHasta, $motivo ?: null, $id]);
        } elseif ($tabla === 'empresas') {
            $sync = $conexion->prepare("UPDATE emprendimientos SET baneado = 1, baneado_hasta = ?, motivo_baneo = ? WHERE id = ?");
            $sync->execute([$baneadoHasta, $motivo ?: null, $id]);
        }

        $tipoTexto = $baneadoHasta ? "temporalmente por $duracion día(s) (hasta el " . date('d/m/Y H:i', strtotime($baneadoHasta)) . ")" : "permanentemente";
        $_SESSION['admin_exito'] = "La cuenta ha sido suspendida $tipoTexto con éxito.";

        header('Location: ban.php?pagina=panel_admin');
        exit;
    }

    public function desbanearCuenta()
    {
        if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo'] ?? '') !== 'admin') {
            accesoDenegado('Acción reservada exclusivamente para el Administrador del sistema.', 'Panel de Administración');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $tabla = $_POST['tabla'] ?? '';
        $tablasValidas = ['empresas', 'emprendimientos', 'usuarios_may', 'usuarios_men'];

        if ($id > 0 && in_array($tabla, $tablasValidas, true)) {
            require __DIR__ . '/../../config/database.php';
            $sql = "UPDATE $tabla SET baneado = 0, baneado_hasta = NULL, motivo_baneo = NULL WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$id]);

            if ($tabla === 'emprendimientos') {
                $sync = $conexion->prepare("UPDATE empresas SET baneado = 0, baneado_hasta = NULL, motivo_baneo = NULL WHERE id = ?");
                $sync->execute([$id]);
            } elseif ($tabla === 'empresas') {
                $sync = $conexion->prepare("UPDATE emprendimientos SET baneado = 0, baneado_hasta = NULL, motivo_baneo = NULL WHERE id = ?");
                $sync->execute([$id]);
            }

            $_SESSION['admin_exito'] = 'La suspensión de la cuenta ha sido revocada exitosamente.';
        }

        header('Location: ban.php?pagina=panel_admin');
        exit;
    }

    public function bloquearCorreo()
    {
        if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo'] ?? '') !== 'admin') {
            accesoDenegado('Acción reservada exclusivamente para el Administrador del sistema.', 'Panel de Administración');
        }

        $correo = trim($_POST['correo'] ?? '');

        if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['admin_error'] = 'Debes ingresar un correo electrónico válido para bloquear.';
            header('Location: ban.php?pagina=panel_admin#seccionBloqueos');
            exit;
        }

        require __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../Models/BloqueoCorreo.php';
        $modeloBloqueo = new BloqueoCorreo($conexion);
        $modeloBloqueo->bloquear($correo);

        $_SESSION['admin_exito'] = 'El correo ' . htmlspecialchars($correo) . ' ha sido bloqueado exitosamente.';
        header('Location: ban.php?pagina=panel_admin#seccionBloqueos');
        exit;
    }

    public function desbloquearCorreo()
    {
        if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo'] ?? '') !== 'admin') {
            accesoDenegado('Acción reservada exclusivamente para el Administrador del sistema.', 'Panel de Administración');
        }

        $correo = trim($_POST['correo'] ?? '');

        if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['admin_error'] = 'Debes especificar un correo válido para desbloquear.';
            header('Location: ban.php?pagina=panel_admin#seccionBloqueos');
            exit;
        }

        require __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../Models/BloqueoCorreo.php';
        $modeloBloqueo = new BloqueoCorreo($conexion);
        $modeloBloqueo->desbloquear($correo);

        $_SESSION['admin_exito'] = 'El correo ' . htmlspecialchars($correo) . ' ha sido desbloqueado y sus intentos han sido restablecidos a 0.';
        header('Location: ban.php?pagina=panel_admin#seccionBloqueos');
        exit;
    }

    public function panelEmpleado()
    {
        if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo'] ?? '') !== 'empleado') {
            accesoDenegado('El panel de empleado es de uso exclusivo para jóvenes y postulantes registrados.', 'Panel de Empleado');
        }

        require __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../Models/Usuario.php';

        $candidatoId = (int) $_SESSION['usuario_id'];
        $candidatoTipo = !empty($_SESSION['es_menor']) ? 'menor' : 'mayor';

        $modeloUsuario = new Usuario($conexion);
        $usuario = $modeloUsuario->obtenerPorId($candidatoId, $candidatoTipo);

        if (!$usuario) {
            $_SESSION['flash_error'] = 'No se encontró la información del usuario.';
            header('Location: ban.php?pagina=inicio');
            exit;
        }

        vista('panel_empleado', [
            'usuario'       => $usuario,
            'candidatoTipo' => $candidatoTipo,
            'mensajeExito'  => $_SESSION['flash_exito'] ?? '',
            'mensajeError'  => $_SESSION['flash_error'] ?? '',
        ]);
        unset($_SESSION['flash_exito'], $_SESSION['flash_error']);
    }

    public function cambiarCorreoEmpleado()
    {
        if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo'] ?? '') !== 'empleado') {
            accesoDenegado('Acción permitida únicamente para candidatos empleados.', 'Panel de Empleado');
        }

        require __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../Models/Usuario.php';

        $candidatoId = (int) $_SESSION['usuario_id'];
        $candidatoTipo = !empty($_SESSION['es_menor']) ? 'menor' : 'mayor';
        $tabla = ($candidatoTipo === 'menor') ? 'usuarios_men' : 'usuarios_may';

        $modeloUsuario = new Usuario($conexion);
        $usuario = $modeloUsuario->obtenerPorId($candidatoId, $candidatoTipo);

        $nuevoCorreo = trim($_POST['nuevo_correo'] ?? '');
        $contrasenaActual = $_POST['contrasena_actual'] ?? '';

        if (empty($nuevoCorreo) || empty($contrasenaActual)) {
            $_SESSION['flash_error'] = 'Debes ingresar el nuevo correo y tu contraseña actual para confirmar el cambio.';
            header('Location: ban.php?pagina=panel_empleado#seccionCorreo');
            exit;
        }

        if (strpos($nuevoCorreo, '@') === false || !filter_var($nuevoCorreo, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Por favor, introduce una dirección de correo electrónico válida con signo @.';
            header('Location: ban.php?pagina=panel_empleado#seccionCorreo');
            exit;
        }

        if (strtolower($nuevoCorreo) === strtolower($usuario['correo'])) {
            $_SESSION['flash_error'] = 'El correo electrónico ingresado es idéntico al actual.';
            header('Location: ban.php?pagina=panel_empleado#seccionCorreo');
            exit;
        }

        if (!password_verify($contrasenaActual, $usuario['contrasena'])) {
            $_SESSION['flash_error'] = 'La contraseña actual ingresada es incorrecta.';
            header('Location: ban.php?pagina=panel_empleado#seccionCorreo');
            exit;
        }

        // Validar que el nuevo correo no esté en uso por otra cuenta
        $enUso = false;
        $stmt1 = $conexion->prepare("SELECT id FROM usuarios_may WHERE correo = ? AND NOT (id = ? AND ? = 'mayor') LIMIT 1");
        $stmt1->execute([$nuevoCorreo, $candidatoId, $candidatoTipo]);
        if ($stmt1->fetch()) $enUso = true;

        if (!$enUso) {
            $stmt2 = $conexion->prepare("SELECT id FROM usuarios_men WHERE correo = ? AND NOT (id = ? AND ? = 'menor') LIMIT 1");
            $stmt2->execute([$nuevoCorreo, $candidatoId, $candidatoTipo]);
            if ($stmt2->fetch()) $enUso = true;
        }

        if (!$enUso) {
            $stmt3 = $conexion->prepare("SELECT id FROM empresas WHERE correo = ? LIMIT 1");
            $stmt3->execute([$nuevoCorreo]);
            if ($stmt3->fetch()) $enUso = true;
        }

        if (!$enUso) {
            $stmt4 = $conexion->prepare("SELECT id FROM emprendimientos WHERE correo = ? LIMIT 1");
            $stmt4->execute([$nuevoCorreo]);
            if ($stmt4->fetch()) $enUso = true;
        }

        if (!$enUso) {
            $stmt5 = $conexion->prepare("SELECT id FROM usuarios_admin WHERE correo = ? LIMIT 1");
            $stmt5->execute([$nuevoCorreo]);
            if ($stmt5->fetch()) $enUso = true;
        }

        if ($enUso) {
            $_SESSION['flash_error'] = 'El correo electrónico ya se encuentra registrado por otra cuenta en el sistema.';
            header('Location: ban.php?pagina=panel_empleado#seccionCorreo');
            exit;
        }

        $stmtUp = $conexion->prepare("UPDATE $tabla SET correo = ? WHERE id = ?");
        $stmtUp->execute([$nuevoCorreo, $candidatoId]);

        $_SESSION['correo'] = $nuevoCorreo;
        $_SESSION['flash_exito'] = 'Tu correo electrónico ha sido actualizado exitosamente a: ' . $nuevoCorreo;
        header('Location: ban.php?pagina=panel_empleado#seccionCorreo');
        exit;
    }

    public function cambiarContrasenaEmpleado()
    {
        if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo'] ?? '') !== 'empleado') {
            accesoDenegado('Acción permitida únicamente para candidatos empleados.', 'Panel de Empleado');
        }

        require __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../Models/Usuario.php';

        $candidatoId = (int) $_SESSION['usuario_id'];
        $candidatoTipo = !empty($_SESSION['es_menor']) ? 'menor' : 'mayor';
        $tabla = ($candidatoTipo === 'menor') ? 'usuarios_men' : 'usuarios_may';

        $modeloUsuario = new Usuario($conexion);
        $usuario = $modeloUsuario->obtenerPorId($candidatoId, $candidatoTipo);

        $contrasenaActual = $_POST['contrasena_actual'] ?? '';
        $nuevaContrasena = $_POST['nueva_contrasena'] ?? '';
        $confirmarNuevaContrasena = $_POST['confirmar_nueva_contrasena'] ?? '';

        if (empty($contrasenaActual) || empty($nuevaContrasena) || empty($confirmarNuevaContrasena)) {
            $_SESSION['flash_error'] = 'Debes completar todos los campos para cambiar tu contraseña.';
            header('Location: ban.php?pagina=panel_empleado#seccionContrasena');
            exit;
        }

        if (!password_verify($contrasenaActual, $usuario['contrasena'])) {
            $_SESSION['flash_error'] = 'La contraseña actual ingresada es incorrecta.';
            header('Location: ban.php?pagina=panel_empleado#seccionContrasena');
            exit;
        }

        if (strlen($nuevaContrasena) < 6) {
            $_SESSION['flash_error'] = 'La nueva contraseña debe tener al menos 6 caracteres.';
            header('Location: ban.php?pagina=panel_empleado#seccionContrasena');
            exit;
        }

        if (!preg_match('/[A-Z]/', $nuevaContrasena)) {
            $_SESSION['flash_error'] = 'La nueva contraseña debe incluir al menos una letra mayúscula.';
            header('Location: ban.php?pagina=panel_empleado#seccionContrasena');
            exit;
        }

        if (!preg_match('/[a-z]/', $nuevaContrasena)) {
            $_SESSION['flash_error'] = 'La nueva contraseña debe incluir al menos una letra minúscula.';
            header('Location: ban.php?pagina=panel_empleado#seccionContrasena');
            exit;
        }

        if (!preg_match('/[0-9]/', $nuevaContrasena)) {
            $_SESSION['flash_error'] = 'La nueva contraseña debe incluir al menos un número.';
            header('Location: ban.php?pagina=panel_empleado#seccionContrasena');
            exit;
        }

        if (!preg_match('/[^a-zA-Z0-9]/', $nuevaContrasena)) {
            $_SESSION['flash_error'] = 'La nueva contraseña debe incluir al menos un carácter especial (ej. !@#$%&*).';
            header('Location: ban.php?pagina=panel_empleado#seccionContrasena');
            exit;
        }

        if ($nuevaContrasena !== $confirmarNuevaContrasena) {
            $_SESSION['flash_error'] = 'La nueva contraseña y su confirmación no coinciden.';
            header('Location: ban.php?pagina=panel_empleado#seccionContrasena');
            exit;
        }

        $hashNuevo = password_hash($nuevaContrasena, PASSWORD_DEFAULT);
        $stmtUp = $conexion->prepare("UPDATE $tabla SET contrasena = ? WHERE id = ?");
        $stmtUp->execute([$hashNuevo, $candidatoId]);

        $_SESSION['flash_exito'] = 'Tu contraseña ha sido actualizada exitosamente.';
        header('Location: ban.php?pagina=panel_empleado#seccionContrasena');
        exit;
    }

    public function actualizarCvEmpleado()
    {
        if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo'] ?? '') !== 'empleado') {
            accesoDenegado('Acción permitida únicamente para candidatos empleados.', 'Panel de Empleado');
        }

        require __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../Models/Usuario.php';

        $candidatoId = (int) $_SESSION['usuario_id'];
        $candidatoTipo = !empty($_SESSION['es_menor']) ? 'menor' : 'mayor';
        $tabla = ($candidatoTipo === 'menor') ? 'usuarios_men' : 'usuarios_may';

        $modeloUsuario = new Usuario($conexion);
        $usuario = $modeloUsuario->obtenerPorId($candidatoId, $candidatoTipo);

        if (!isset($_FILES['cv_archivo']) || $_FILES['cv_archivo']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Por favor selecciona un archivo PDF válido de tu Curriculum Vitae.';
            header('Location: ban.php?pagina=panel_empleado#seccionCv');
            exit;
        }

        $extension = strtolower(pathinfo($_FILES['cv_archivo']['name'] ?? '', PATHINFO_EXTENSION));
        if ($extension !== 'pdf') {
            $_SESSION['flash_error'] = 'El archivo de Curriculum Vitae debe ser exclusivamente un documento en formato PDF (.pdf).';
            header('Location: ban.php?pagina=panel_empleado#seccionCv');
            exit;
        }

        $nuevoCvPath = subirArchivo('cv_archivo', 'cv', 'cv');
        if (!$nuevoCvPath) {
            $_SESSION['flash_error'] = 'No se pudo subir el archivo. Verifica que no sobrepase el tamaño límite de 5MB.';
            header('Location: ban.php?pagina=panel_empleado#seccionCv');
            exit;
        }

        // Eliminar el CV anterior si existía físicamente
        $cvAnterior = $usuario['cv_path'] ?? null;
        if (!empty($cvAnterior)) {
            eliminarArchivo($cvAnterior);
        }

        $stmtUp = $conexion->prepare("UPDATE $tabla SET cv_path = ? WHERE id = ?");
        $stmtUp->execute([$nuevoCvPath, $candidatoId]);

        $_SESSION['flash_exito'] = '¡Tu Curriculum Vitae ha sido actualizado con éxito! El archivo anterior fue eliminado del servidor.';
        header('Location: ban.php?pagina=panel_empleado#seccionCv');
        exit;
    }

    public function eliminarCvEmpleado()
    {
        if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo'] ?? '') !== 'empleado') {
            accesoDenegado('Acción permitida únicamente para candidatos empleados.', 'Panel de Empleado');
        }

        require __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../Models/Usuario.php';

        $candidatoId = (int) $_SESSION['usuario_id'];
        $candidatoTipo = !empty($_SESSION['es_menor']) ? 'menor' : 'mayor';
        $tabla = ($candidatoTipo === 'menor') ? 'usuarios_men' : 'usuarios_may';

        $modeloUsuario = new Usuario($conexion);
        $usuario = $modeloUsuario->obtenerPorId($candidatoId, $candidatoTipo);

        $cvAnterior = $usuario['cv_path'] ?? null;
        if (!empty($cvAnterior)) {
            eliminarArchivo($cvAnterior);
        }

        $stmtUp = $conexion->prepare("UPDATE $tabla SET cv_path = NULL WHERE id = ?");
        $stmtUp->execute([$candidatoId]);

        $_SESSION['flash_exito'] = 'Tu Curriculum Vitae ha sido eliminado correctamente.';
        header('Location: ban.php?pagina=panel_empleado#seccionCv');
        exit;
    }
}
