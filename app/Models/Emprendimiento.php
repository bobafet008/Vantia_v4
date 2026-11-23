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
                motivo_baneo VARCHAR(60) DEFAULT NULL,
                creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
            $this->conexion->exec($sql);

            // 1. Compatibilidad para creado_en / creada_en
            $colCreadoEn = $this->conexion->query("SHOW COLUMNS FROM emprendimientos LIKE 'creado_en'")->fetch();
            $colCreadaEn = $this->conexion->query("SHOW COLUMNS FROM emprendimientos LIKE 'creada_en'")->fetch();
            if (!$colCreadoEn) {
                if ($colCreadaEn) {
                    $this->conexion->exec("ALTER TABLE emprendimientos ADD COLUMN creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
                    $this->conexion->exec("UPDATE emprendimientos SET creado_en = creada_en WHERE creada_en IS NOT NULL");
                } else {
                    $this->conexion->exec("ALTER TABLE emprendimientos ADD COLUMN creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
                }
            }

            // 2. Compatibilidad para verificado / verificada
            $colVerificado = $this->conexion->query("SHOW COLUMNS FROM emprendimientos LIKE 'verificado'")->fetch();
            $colVerificada = $this->conexion->query("SHOW COLUMNS FROM emprendimientos LIKE 'verificada'")->fetch();
            if (!$colVerificado) {
                if ($colVerificada) {
                    $this->conexion->exec("ALTER TABLE emprendimientos ADD COLUMN verificado TINYINT(1) NOT NULL DEFAULT 1");
                    $this->conexion->exec("UPDATE emprendimientos SET verificado = verificada");
                } else {
                    $this->conexion->exec("ALTER TABLE emprendimientos ADD COLUMN verificado TINYINT(1) NOT NULL DEFAULT 1");
                }
            }

            // 3. Compatibilidad para representante / titular_nombre
            $colRep = $this->conexion->query("SHOW COLUMNS FROM emprendimientos LIKE 'representante'")->fetch();
            $colTit = $this->conexion->query("SHOW COLUMNS FROM emprendimientos LIKE 'titular_nombre'")->fetch();
            if (!$colRep) {
                $this->conexion->exec("ALTER TABLE emprendimientos ADD COLUMN representante VARCHAR(40) NULL");
                if ($colTit) {
                    $this->conexion->exec("UPDATE emprendimientos SET representante = titular_nombre WHERE titular_nombre IS NOT NULL");
                }
            }

            // 4. Compatibilidad para ci / titular_ci / nit_ci
            $colCi = $this->conexion->query("SHOW COLUMNS FROM emprendimientos LIKE 'ci'")->fetch();
            $colTitCi = $this->conexion->query("SHOW COLUMNS FROM emprendimientos LIKE 'titular_ci'")->fetch();
            $colNitCi = $this->conexion->query("SHOW COLUMNS FROM emprendimientos LIKE 'nit_ci'")->fetch();
            if (!$colCi) {
                $this->conexion->exec("ALTER TABLE emprendimientos ADD COLUMN ci BIGINT NULL");
                if ($colTitCi) {
                    $this->conexion->exec("UPDATE emprendimientos SET ci = titular_ci WHERE titular_ci IS NOT NULL");
                } elseif ($colNitCi) {
                    $this->conexion->exec("UPDATE emprendimientos SET ci = nit_ci WHERE nit_ci IS NOT NULL");
                }
            }

            // 5. Compatibilidad para foto_puesto
            $colFoto = $this->conexion->query("SHOW COLUMNS FROM emprendimientos LIKE 'foto_puesto'")->fetch();
            if (!$colFoto) {
                $this->conexion->exec("ALTER TABLE emprendimientos ADD COLUMN foto_puesto VARCHAR(80) NOT NULL DEFAULT 'uploads/roe/roe_default.png'");
            }
        } catch (Exception $e) {
            error_log('Error en asegurarTabla emprendimientos: ' . $e->getMessage());
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
        try {
            $sql = 'SELECT * FROM emprendimientos ORDER BY creado_en DESC';
            return $this->conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            try {
                $sql = 'SELECT * FROM emprendimientos ORDER BY creada_en DESC';
                return $this->conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e2) {
                return $this->conexion->query('SELECT * FROM emprendimientos ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
            }
        }
    }

    public function toggleVerificacion($id)
    {
        $this->asegurarTabla();
        try {
            $sql = 'UPDATE emprendimientos SET verificado = 1 - verificado WHERE id = :id';
            $consulta = $this->conexion->prepare($sql);
            return $consulta->execute(['id' => (int) $id]);
        } catch (Exception $e) {
            try {
                $sql = 'UPDATE emprendimientos SET verificada = 1 - verificada WHERE id = :id';
                $consulta = $this->conexion->prepare($sql);
                return $consulta->execute(['id' => (int) $id]);
            } catch (Exception $e2) {
                return false;
            }
        }
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
