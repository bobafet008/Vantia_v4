<?php

class MensajeSoporte
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
            $sql = "CREATE TABLE IF NOT EXISTS mensajes_soporte (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_may_id INT DEFAULT NULL,
                usuario_men_id INT DEFAULT NULL,
                empresa_id INT DEFAULT NULL,
                emprendimiento_id INT DEFAULT NULL,
                nombre VARCHAR(100) NOT NULL,
                email VARCHAR(150) NOT NULL,
                rol VARCHAR(50) DEFAULT 'Usuario',
                asunto VARCHAR(150) NOT NULL,
                mensaje TEXT NOT NULL,
                estado ENUM('pendiente', 'respondido') DEFAULT 'pendiente',
                ip_origen VARCHAR(45) NULL,
                creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (email),
                INDEX (creado_en),
                INDEX (estado),
                INDEX (usuario_may_id),
                INDEX (usuario_men_id),
                INDEX (empresa_id),
                INDEX (emprendimiento_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
            $this->conexion->exec($sql);

            $cols = [
                'usuario_may_id'    => "ALTER TABLE mensajes_soporte ADD COLUMN usuario_may_id INT DEFAULT NULL AFTER id",
                'usuario_men_id'    => "ALTER TABLE mensajes_soporte ADD COLUMN usuario_men_id INT DEFAULT NULL AFTER usuario_may_id",
                'empresa_id'        => "ALTER TABLE mensajes_soporte ADD COLUMN empresa_id INT DEFAULT NULL AFTER usuario_men_id",
                'emprendimiento_id' => "ALTER TABLE mensajes_soporte ADD COLUMN emprendimiento_id INT DEFAULT NULL AFTER empresa_id",
            ];

            foreach ($cols as $col => $sqlAdd) {
                $existe = $this->conexion->query("SHOW COLUMNS FROM mensajes_soporte LIKE '$col'")->fetch();
                if (!$existe) {
                    $this->conexion->exec($sqlAdd);
                }
            }

            $fks = [
                'fk_soporte_usuario_may'    => "ALTER TABLE mensajes_soporte ADD CONSTRAINT fk_soporte_usuario_may FOREIGN KEY (usuario_may_id) REFERENCES usuarios_may(id) ON DELETE SET NULL ON UPDATE CASCADE",
                'fk_soporte_usuario_men'    => "ALTER TABLE mensajes_soporte ADD CONSTRAINT fk_soporte_usuario_men FOREIGN KEY (usuario_men_id) REFERENCES usuarios_men(id) ON DELETE SET NULL ON UPDATE CASCADE",
                'fk_soporte_empresa'        => "ALTER TABLE mensajes_soporte ADD CONSTRAINT fk_soporte_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE SET NULL ON UPDATE CASCADE",
                'fk_soporte_emprendimiento' => "ALTER TABLE mensajes_soporte ADD CONSTRAINT fk_soporte_emprendimiento FOREIGN KEY (emprendimiento_id) REFERENCES emprendimientos(id) ON DELETE SET NULL ON UPDATE CASCADE",
            ];

            foreach ($fks as $fkName => $sqlFk) {
                try {
                    $fkExiste = $this->conexion->query("
                        SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mensajes_soporte' AND CONSTRAINT_NAME = '$fkName'
                    ")->fetch();
                    if (!$fkExiste) {
                        $this->conexion->exec($sqlFk);
                    }
                } catch (Exception $eFk) {}
            }
        } catch (Exception $e) {
            error_log('Error en asegurarTabla mensajes_soporte: ' . $e->getMessage());
        }
    }

    public function crear($nombre, $email, $rol, $asunto, $mensaje, $ipOrigen = null, $usuarioMayId = null, $usuarioMenId = null, $empresaId = null, $emprendimientoId = null)
    {
        $this->asegurarTabla();
        $ip = $ipOrigen ?: ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

        $sql = "INSERT INTO mensajes_soporte (
                    usuario_may_id, usuario_men_id, empresa_id, emprendimiento_id,
                    nombre, email, rol, asunto, mensaje, estado, ip_origen
                ) VALUES (
                    :usuario_may_id, :usuario_men_id, :empresa_id, :emprendimiento_id,
                    :nombre, :email, :rol, :asunto, :mensaje, 'pendiente', :ip
                )";
        $stmt = $this->conexion->prepare($sql);
        $ok = $stmt->execute([
            'usuario_may_id'    => $usuarioMayId ? (int) $usuarioMayId : null,
            'usuario_men_id'    => $usuarioMenId ? (int) $usuarioMenId : null,
            'empresa_id'        => $empresaId ? (int) $empresaId : null,
            'emprendimiento_id' => $emprendimientoId ? (int) $emprendimientoId : null,
            'nombre'            => substr(trim($nombre), 0, 100),
            'email'             => strtolower(substr(trim($email), 0, 150)),
            'rol'               => substr(trim($rol), 0, 50),
            'asunto'            => substr(trim($asunto), 0, 150),
            'mensaje'           => trim($mensaje),
            'ip'                => substr($ip, 0, 45)
        ]);

        return $ok ? (int) $this->conexion->lastInsertId() : false;
    }

    public function obtenerTodos($limite = 150)
    {
        $sql = "SELECT * FROM mensajes_soporte ORDER BY creado_en DESC LIMIT " . (int) $limite;
        return $this->conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorFecha($fecha)
    {
        $sql = "SELECT * FROM mensajes_soporte WHERE DATE(creado_en) = :fecha ORDER BY creado_en DESC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute(['fecha' => $fecha]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contar()
    {
        return (int) $this->conexion->query("SELECT COUNT(*) FROM mensajes_soporte")->fetchColumn();
    }

    public function contarPendientes()
    {
        return (int) $this->conexion->query("SELECT COUNT(*) FROM mensajes_soporte WHERE estado = 'pendiente'")->fetchColumn();
    }

    public function cambiarEstado($id, $nuevoEstado)
    {
        $estadoValido = ($nuevoEstado === 'respondido') ? 'respondido' : 'pendiente';
        $sql = "UPDATE mensajes_soporte SET estado = :estado WHERE id = :id";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            'estado' => $estadoValido,
            'id'     => (int) $id
        ]);
    }

    public function eliminar($id)
    {
        $sql = "DELETE FROM mensajes_soporte WHERE id = :id";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute(['id' => (int) $id]);
    }
}
