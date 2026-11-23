<?php
class Empresa
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->asegurarColumnaTipoCuenta();
    }

    private function asegurarColumnaTipoCuenta()
    {
        try {
            $col = $this->conexion->query("SHOW COLUMNS FROM empresas LIKE 'tipo_cuenta'")->fetch();
            if (!$col) {
                $this->conexion->exec("ALTER TABLE empresas ADD COLUMN tipo_cuenta VARCHAR(20) NOT NULL DEFAULT 'empresa' AFTER nombre");
            }
        } catch (Exception $e) {
            // Silencioso
        }
    }

    public function crear($datos)
    {
        $this->asegurarColumnaTipoCuenta();
        $tipoCuenta = $datos['tipo_cuenta'] ?? 'empresa';

        $sql = 'INSERT INTO empresas (
                    nombre, tipo_cuenta, representante, nit, roe, correo, contrasena, telefono,
                    descripcion, verificada
                ) VALUES (
                    :nombre, :tipo_cuenta, :representante, :nit, :roe, :correo, :contrasena, :telefono,
                    :descripcion, :verificada
                )';
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute([
            'nombre'               => $datos['nombre'],
            'tipo_cuenta'          => $tipoCuenta,
            'representante'        => $datos['representante'],
            'nit'                  => $datos['nit'] ?? null,
            'roe'                  => $datos['roe'] ?? 'uploads/roe/roe_default.png',
            'correo'               => $datos['correo'],
            'contrasena'           => $datos['contrasena'],
            'telefono'             => $datos['telefono'],
            'descripcion'          => $datos['descripcion'] ?? null,
            'verificada'           => $datos['verificada'] ?? 1,
        ]);
    }

    public function crearConId($datos)
    {
        $this->asegurarColumnaTipoCuenta();
        $tipoCuenta = $datos['tipo_cuenta'] ?? 'emprendimiento';

        $sql = 'INSERT INTO empresas (
                    id, nombre, tipo_cuenta, representante, nit, roe, correo, contrasena, telefono,
                    descripcion, verificada
                ) VALUES (
                    :id, :nombre, :tipo_cuenta, :representante, :nit, :roe, :correo, :contrasena, :telefono,
                    :descripcion, :verificada
                ) ON DUPLICATE KEY UPDATE 
                    nombre = VALUES(nombre),
                    representante = VALUES(representante),
                    tipo_cuenta = VALUES(tipo_cuenta),
                    nit = VALUES(nit),
                    roe = VALUES(roe),
                    telefono = VALUES(telefono),
                    descripcion = VALUES(descripcion)';
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute([
            'id'                   => (int) $datos['id'],
            'nombre'               => $datos['nombre'],
            'tipo_cuenta'          => $tipoCuenta,
            'representante'        => $datos['representante'],
            'nit'                  => $datos['nit'] ?? null,
            'roe'                  => $datos['roe'] ?? 'uploads/roe/roe_default.png',
            'correo'               => $datos['correo'],
            'contrasena'           => $datos['contrasena'],
            'telefono'             => $datos['telefono'],
            'descripcion'          => $datos['descripcion'] ?? null,
            'verificada'           => $datos['verificada'] ?? 1,
        ]);
    }

    public function buscarPorCorreo($correo)
    {
        $sql = 'SELECT * FROM empresas WHERE correo = :correo LIMIT 1';
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute(['correo' => $correo]);
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id)
    {
        $sql = 'SELECT * FROM empresas WHERE id = :id LIMIT 1';
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute(['id' => (int) $id]);
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerTodas()
    {
        $sql = 'SELECT * FROM empresas ORDER BY creada_en DESC';
        return $this->conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function toggleVerificacion($id)
    {
        $sql = 'UPDATE empresas SET verificada = 1 - verificada WHERE id = :id';
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute(['id' => (int) $id]);
    }

    public function eliminar($id)
    {
        $sql = 'DELETE FROM empresas WHERE id = :id';
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute(['id' => (int) $id]);
    }

    public function contar()
    {
        return (int) $this->conexion->query('SELECT COUNT(*) FROM empresas')->fetchColumn();
    }
}
