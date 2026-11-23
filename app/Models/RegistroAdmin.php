<?php
class RegistroAdmin
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->asegurarTabla();
    }

    private function asegurarTabla()
    {
        try {
            // Eliminar tablas obsoletas si aun existieran en el entorno
            $this->conexion->exec("DROP TABLE IF EXISTS bloqueos_correo");
            $this->conexion->exec("DROP TABLE IF EXISTS bloqueo_correos");
            $this->conexion->exec("DROP VIEW IF EXISTS registro_admin");
            $this->conexion->exec("DROP TABLE IF EXISTS registro_admin");

            $sql = "CREATE TABLE IF NOT EXISTS registros_admin (
                id INT AUTO_INCREMENT PRIMARY KEY,
                admin_id INT DEFAULT NULL,
                usuario_may_id INT DEFAULT NULL,
                usuario_men_id INT DEFAULT NULL,
                empresa_id INT DEFAULT NULL,
                emprendimiento_id INT DEFAULT NULL,
                mensaje_soporte_id INT DEFAULT NULL,
                tipo_evento ENUM('reporte', 'baneo', 'desbaneo', 'nuevo_usuario', 'bloqueo_correo', 'verificacion', 'accion_admin', 'soporte') NOT NULL,
                titulo VARCHAR(50) NOT NULL,
                descripcion VARCHAR(80) NOT NULL,
                usuario_afectado VARCHAR(50) DEFAULT NULL,
                ip_origen VARCHAR(45) DEFAULT NULL,
                fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (admin_id),
                INDEX (usuario_may_id),
                INDEX (usuario_men_id),
                INDEX (empresa_id),
                INDEX (emprendimiento_id),
                INDEX (mensaje_soporte_id),
                INDEX (tipo_evento),
                INDEX (fecha_registro)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
            $this->conexion->exec($sql);

            // Columnas requeridas para garantizar conexion relacional completa
            $columnas = [
                'admin_id'           => "ALTER TABLE registros_admin ADD COLUMN admin_id INT DEFAULT NULL AFTER id",
                'usuario_may_id'     => "ALTER TABLE registros_admin ADD COLUMN usuario_may_id INT DEFAULT NULL AFTER admin_id",
                'usuario_men_id'     => "ALTER TABLE registros_admin ADD COLUMN usuario_men_id INT DEFAULT NULL AFTER usuario_may_id",
                'empresa_id'         => "ALTER TABLE registros_admin ADD COLUMN empresa_id INT DEFAULT NULL AFTER usuario_men_id",
                'emprendimiento_id'  => "ALTER TABLE registros_admin ADD COLUMN emprendimiento_id INT DEFAULT NULL AFTER empresa_id",
                'mensaje_soporte_id' => "ALTER TABLE registros_admin ADD COLUMN mensaje_soporte_id INT DEFAULT NULL AFTER emprendimiento_id",
            ];

            foreach ($columnas as $col => $sqlAdd) {
                $existe = $this->conexion->query("SHOW COLUMNS FROM registros_admin LIKE '$col'")->fetch();
                if (!$existe) {
                    $this->conexion->exec($sqlAdd);
                }
            }

            // Actualizar definicion de tipo_evento para admitir todos los tipos
            try {
                $this->conexion->exec("ALTER TABLE registros_admin MODIFY COLUMN tipo_evento ENUM('reporte', 'baneo', 'desbaneo', 'nuevo_usuario', 'bloqueo_correo', 'verificacion', 'accion_admin', 'soporte') NOT NULL");
            } catch (Exception $e) {}

            // Asegurar llaves foraneas relacionales
            $fks = [
                'fk_registros_admin_admin'          => "ALTER TABLE registros_admin ADD CONSTRAINT fk_registros_admin_admin FOREIGN KEY (admin_id) REFERENCES usuarios_admin(id) ON DELETE SET NULL ON UPDATE CASCADE",
                'fk_registros_admin_usuario_may'    => "ALTER TABLE registros_admin ADD CONSTRAINT fk_registros_admin_usuario_may FOREIGN KEY (usuario_may_id) REFERENCES usuarios_may(id) ON DELETE SET NULL ON UPDATE CASCADE",
                'fk_registros_admin_usuario_men'    => "ALTER TABLE registros_admin ADD CONSTRAINT fk_registros_admin_usuario_men FOREIGN KEY (usuario_men_id) REFERENCES usuarios_men(id) ON DELETE SET NULL ON UPDATE CASCADE",
                'fk_registros_admin_empresa'        => "ALTER TABLE registros_admin ADD CONSTRAINT fk_registros_admin_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE SET NULL ON UPDATE CASCADE",
                'fk_registros_admin_emprendimiento' => "ALTER TABLE registros_admin ADD CONSTRAINT fk_registros_admin_emprendimiento FOREIGN KEY (emprendimiento_id) REFERENCES emprendimientos(id) ON DELETE SET NULL ON UPDATE CASCADE",
                'fk_registros_admin_soporte'        => "ALTER TABLE registros_admin ADD CONSTRAINT fk_registros_admin_soporte FOREIGN KEY (mensaje_soporte_id) REFERENCES mensajes_soporte(id) ON DELETE SET NULL ON UPDATE CASCADE",
            ];

            foreach ($fks as $fkName => $sqlFk) {
                try {
                    $fkExiste = $this->conexion->query("
                        SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS 
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'registros_admin' AND CONSTRAINT_NAME = '$fkName'
                    ")->fetch();
                    if (!$fkExiste) {
                        $this->conexion->exec($sqlFk);
                    }
                } catch (Exception $eFk) {}
            }
        } catch (Exception $e) {
            error_log('Error en asegurarTabla de registros_admin: ' . $e->getMessage());
        }
    }

    public function registrar($adminId, $tipoEvento, $titulo, $descripcion, $usuarioAfectado = null, $ipOrigen = null, $relaciones = [])
    {
        try {
            $ip = $ipOrigen ?: ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

            // Determinar admin_id valido si esta logueado como administrador
            $adminIdFinal = $adminId ? (int) $adminId : (!empty($relaciones['admin_id']) ? (int) $relaciones['admin_id'] : null);
            if (!$adminIdFinal && !empty($_SESSION['usuario_id']) && (($_SESSION['tabla_usuario'] ?? '') === 'usuarios_admin' || ($_SESSION['tipo'] ?? '') === 'admin')) {
                $adminIdFinal = (int) $_SESSION['usuario_id'];
            }

            if ($adminIdFinal) {
                try {
                    $adminExiste = $this->conexion->query("SELECT id FROM usuarios_admin WHERE id = $adminIdFinal LIMIT 1")->fetch();
                    if (!$adminExiste) {
                        $adminIdFinal = null;
                    }
                } catch (Exception $e) {
                    $adminIdFinal = null;
                }
            }

            // Identificar IDs de relaciones explicitas
            $usuarioMayId        = !empty($relaciones['usuario_may_id']) ? (int) $relaciones['usuario_may_id'] : null;
            $usuarioMenId        = !empty($relaciones['usuario_men_id']) ? (int) $relaciones['usuario_men_id'] : null;
            $empresaId           = !empty($relaciones['empresa_id']) ? (int) $relaciones['empresa_id'] : null;
            $emprendimientoId    = !empty($relaciones['emprendimiento_id']) ? (int) $relaciones['emprendimiento_id'] : null;
            $mensajeSoporteId    = !empty($relaciones['mensaje_soporte_id']) ? (int) $relaciones['mensaje_soporte_id'] : null;

            // Deteccion automatica si se paso un correo en usuarioAfectado y no habia relaciones explicitas
            if (!$usuarioMayId && !$usuarioMenId && !$empresaId && !$emprendimientoId && !empty($usuarioAfectado) && filter_var($usuarioAfectado, FILTER_VALIDATE_EMAIL)) {
                try {
                    $stmt = $this->conexion->prepare("SELECT id FROM usuarios_may WHERE correo = ? LIMIT 1");
                    $stmt->execute([$usuarioAfectado]);
                    $encontrado = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($encontrado) {
                        $usuarioMayId = (int) $encontrado['id'];
                    } else {
                        $stmt = $this->conexion->prepare("SELECT id FROM usuarios_men WHERE correo = ? LIMIT 1");
                        $stmt->execute([$usuarioAfectado]);
                        $encontrado = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($encontrado) {
                            $usuarioMenId = (int) $encontrado['id'];
                        } else {
                            $stmt = $this->conexion->prepare("SELECT id FROM emprendimientos WHERE correo = ? LIMIT 1");
                            $stmt->execute([$usuarioAfectado]);
                            $encontrado = $stmt->fetch(PDO::FETCH_ASSOC);
                            if ($encontrado) {
                                $emprendimientoId = (int) $encontrado['id'];
                            } else {
                                $stmt = $this->conexion->prepare("SELECT id FROM empresas WHERE correo = ? LIMIT 1");
                                $stmt->execute([$usuarioAfectado]);
                                $encontrado = $stmt->fetch(PDO::FETCH_ASSOC);
                                if ($encontrado) {
                                    $empresaId = (int) $encontrado['id'];
                                }
                            }
                        }
                    }
                } catch (Exception $eBusqueda) {}
            }

            $sql = 'INSERT INTO registros_admin (
                        admin_id, usuario_may_id, usuario_men_id, empresa_id, emprendimiento_id, mensaje_soporte_id,
                        tipo_evento, titulo, descripcion, usuario_afectado, ip_origen
                    ) VALUES (
                        :admin_id, :usuario_may_id, :usuario_men_id, :empresa_id, :emprendimiento_id, :mensaje_soporte_id,
                        :tipo_evento, :titulo, :descripcion, :usuario_afectado, :ip_origen
                    )';

            $consulta = $this->conexion->prepare($sql);
            return $consulta->execute([
                'admin_id'           => $adminIdFinal,
                'usuario_may_id'     => $usuarioMayId,
                'usuario_men_id'     => $usuarioMenId,
                'empresa_id'         => $empresaId,
                'emprendimiento_id'  => $emprendimientoId,
                'mensaje_soporte_id' => $mensajeSoporteId,
                'tipo_evento'        => $tipoEvento,
                'titulo'             => substr($titulo, 0, 50),
                'descripcion'        => substr($descripcion, 0, 80),
                'usuario_afectado'   => $usuarioAfectado ? substr($usuarioAfectado, 0, 50) : null,
                'ip_origen'          => substr($ip, 0, 45),
            ]);
        } catch (Exception $e) {
            error_log('Error al guardar registros_admin: ' . $e->getMessage());
            return false;
        }
    }

    public function obtenerUltimos($limite = 50, $tipo = null)
    {
        $limiteInt = (int) $limite;
        $select = 'SELECT r.*, a.nombre AS admin_nombre,
                          umay.nombre AS may_nombre, umay.correo AS may_correo,
                          umen.nombre AS men_nombre, umen.correo AS men_correo,
                          emp.nombre AS emp_nombre, emp.correo AS emp_correo,
                          empr.nombre AS empr_nombre, empr.correo AS empr_correo,
                          ms.asunto AS soporte_asunto
                   FROM registros_admin r
                   LEFT JOIN usuarios_admin a ON a.id = r.admin_id
                   LEFT JOIN usuarios_may umay ON umay.id = r.usuario_may_id
                   LEFT JOIN usuarios_men umen ON umen.id = r.usuario_men_id
                   LEFT JOIN empresas emp ON emp.id = r.empresa_id
                   LEFT JOIN emprendimientos empr ON empr.id = r.emprendimiento_id
                   LEFT JOIN mensajes_soporte ms ON ms.id = r.mensaje_soporte_id';

        if ($tipo) {
            $sql = $select . ' WHERE r.tipo_evento = :tipo ORDER BY r.fecha_registro DESC LIMIT ' . $limiteInt;
            $consulta = $this->conexion->prepare($sql);
            $consulta->execute(['tipo' => $tipo]);
            return $consulta->fetchAll(PDO::FETCH_ASSOC);
        }

        $sql = $select . ' ORDER BY r.fecha_registro DESC LIMIT ' . $limiteInt;
        return $this->conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarPorTipo($tipo)
    {
        $sql = 'SELECT COUNT(*) FROM registros_admin WHERE tipo_evento = :tipo';
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute(['tipo' => $tipo]);
        return (int) $consulta->fetchColumn();
    }
}
