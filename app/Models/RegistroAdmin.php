<?php
class RegistroAdmin
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    public function registrar($adminId, $tipoEvento, $titulo, $descripcion, $usuarioAfectado = null, $ipOrigen = null)
    {
        try {
            $ip = $ipOrigen ?: ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
            $sql = 'INSERT INTO registros_admin (admin_id, tipo_evento, titulo, descripcion, usuario_afectado, ip_origen)
                    VALUES (:admin_id, :tipo_evento, :titulo, :descripcion, :usuario_afectado, :ip_origen)';
            $consulta = $this->conexion->prepare($sql);
            return $consulta->execute([
                'admin_id'         => $adminId ? (int) $adminId : null,
                'tipo_evento'      => $tipoEvento,
                'titulo'           => substr($titulo, 0, 50),
                'descripcion'      => substr($descripcion, 0, 80),
                'usuario_afectado' => $usuarioAfectado ? substr($usuarioAfectado, 0, 50) : null,
                'ip_origen'        => substr($ip, 0, 45),
            ]);
        } catch (Exception $e) {
            error_log('Error al guardar registro_admin: ' . $e->getMessage());
            return false;
        }
    }

    public function obtenerUltimos($limite = 50, $tipo = null)
    {
        if ($tipo) {
            $sql = 'SELECT r.*, a.nombre AS admin_nombre 
                    FROM registros_admin r
                    LEFT JOIN usuarios_admin a ON a.id = r.admin_id
                    WHERE r.tipo_evento = :tipo
                    ORDER BY r.fecha_registro DESC LIMIT ' . (int) $limite;
            $consulta = $this->conexion->prepare($sql);
            $consulta->execute(['tipo' => $tipo]);
            return $consulta->fetchAll(PDO::FETCH_ASSOC);
        }

        $sql = 'SELECT r.*, a.nombre AS admin_nombre 
                FROM registros_admin r
                LEFT JOIN usuarios_admin a ON a.id = r.admin_id
                ORDER BY r.fecha_registro DESC LIMIT ' . (int) $limite;
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
