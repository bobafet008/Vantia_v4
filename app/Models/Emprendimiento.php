<?php
class Emprendimiento
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
            $sql = "CREATE TABLE IF NOT EXISTS emprendimientos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(30) NOT NULL,
                representante VARCHAR(40) NOT NULL,
                rubro VARCHAR(30) NOT NULL,
                ci BIGINT DEFAULT NULL,
                correo VARCHAR(50) NOT NULL UNIQUE,
                contrasena VARCHAR(60) NOT NULL,
                telefono BIGINT NOT NULL,
                foto_puesto VARCHAR(80) NOT NULL DEFAULT 'uploads/roe/roe_default.png',
                descripcion TEXT NOT NULL,
                verificado TINYINT(1) NOT NULL DEFAULT 1,
                baneado TINYINT(1) NOT NULL DEFAULT 0,
                baneado_hasta DATETIME DEFAULT NULL,
                motivo_baneo VARCHAR(80) DEFAULT NULL,
                creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            $this->conexion->exec($sql);

            $colNitCi = $this->conexion->query("SHOW COLUMNS FROM emprendimientos LIKE 'nit_ci'")->fetch();
            if ($colNitCi) {
                $this->conexion->exec("ALTER TABLE emprendimientos CHANGE COLUMN nit_ci ci VARCHAR(50) DEFAULT NULL");
            }
        } catch (Exception $e) {
            // Silencioso
        }
    }

    public function crear($datos)
    {
        $this->asegurarTabla();

        $sql = 'INSERT INTO emprendimientos (
                    nombre, representante, rubro, ci, correo, contrasena,
                    telefono, foto_puesto, descripcion, verificado
                ) VALUES (
                    :nombre, :representante, :rubro, :ci, :correo, :contrasena,
                    :telefono, :foto_puesto, :descripcion, :verificado
                )';

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            'nombre'        => $datos['nombre'],
            'representante' => $datos['representante'],
            'rubro'         => $datos['rubro'],
            'ci'            => $datos['ci'] ?? $datos['nit_ci'] ?? null,
            'correo'        => $datos['correo'],
            'contrasena'    => $datos['contrasena'],
            'telefono'      => $datos['telefono'],
            'foto_puesto'   => $datos['foto_puesto'] ?? 'uploads/roe/roe_default.png',
            'descripcion'   => $datos['descripcion'],
            'verificado'    => $datos['verificado'] ?? 1,
        ]);

        return (int) $this->conexion->lastInsertId();
    }

    public function buscarPorCorreo($correo)
    {
        $this->asegurarTabla();
        $sql = 'SELECT * FROM emprendimientos WHERE correo = :correo LIMIT 1';
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute(['correo' => $correo]);
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id)
    {
        $this->asegurarTabla();
        $sql = 'SELECT * FROM emprendimientos WHERE id = :id LIMIT 1';
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute(['id' => (int) $id]);
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerTodos()
    {
        $this->asegurarTabla();
        $sql = 'SELECT * FROM emprendimientos ORDER BY creado_en DESC';
        return $this->conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function toggleVerificacion($id)
    {
        $this->asegurarTabla();
        $sql = 'UPDATE emprendimientos SET verificado = 1 - verificado WHERE id = :id';
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute(['id' => (int) $id]);
    }

    public function eliminar($id)
    {
        $this->asegurarTabla();
        $sql = 'DELETE FROM emprendimientos WHERE id = :id';
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute(['id' => (int) $id]);
    }

    public function contar()
    {
        $this->asegurarTabla();
        return (int) $this->conexion->query('SELECT COUNT(*) FROM emprendimientos')->fetchColumn();
    }
}
