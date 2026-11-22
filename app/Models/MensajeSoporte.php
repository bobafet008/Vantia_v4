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
                INDEX (estado)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            $this->conexion->exec($sql);
        } catch (Exception $e) {}
    }

    public function crear($nombre, $email, $rol, $asunto, $mensaje, $ipOrigen = null)
    {
        $ip = $ipOrigen ?: ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $sql = "INSERT INTO mensajes_soporte (nombre, email, rol, asunto, mensaje, estado, ip_origen)
                VALUES (:nombre, :email, :rol, :asunto, :mensaje, 'pendiente', :ip)";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            'nombre'  => substr(trim($nombre), 0, 100),
            'email'   => strtolower(substr(trim($email), 0, 150)),
            'rol'     => substr(trim($rol), 0, 50),
            'asunto'  => substr(trim($asunto), 0, 150),
            'mensaje' => trim($mensaje),
            'ip'      => substr($ip, 0, 45)
        ]);
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
