<?php
class ControlTutor
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    public function registrar($datos)
    {
        $sql = 'INSERT INTO usuario_tutor (
                    tutor_id, usuario_men_id, tutor_nombre, tutor_ci, tutor_correo, tutor_telefono, tutor_relacion,
                    menor_nombre, menor_correo, menor_telefono, menor_fecha_nacimiento, permiso_tutor_path, cv_path,
                    tipo_control, estado, observaciones
                ) VALUES (
                    :tutor_id, :usuario_men_id, :tutor_nombre, :tutor_ci, :tutor_correo, :tutor_telefono, :tutor_relacion,
                    :menor_nombre, :menor_correo, :menor_telefono, :menor_fecha_nacimiento, :permiso_tutor_path, :cv_path,
                    :tipo_control, :estado, :observaciones
                )';
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute([
            'tutor_id'               => (int) $datos['tutor_id'],
            'usuario_men_id'         => (int) $datos['usuario_men_id'],
            'tutor_nombre'           => $datos['tutor_nombre'],
            'tutor_ci'               => $datos['tutor_ci'] ?? null,
            'tutor_correo'           => $datos['tutor_correo'] ?? null,
            'tutor_telefono'         => $datos['tutor_telefono'],
            'tutor_relacion'         => $datos['tutor_relacion'] ?? 'Tutor legal',
            'menor_nombre'           => $datos['menor_nombre'],
            'menor_correo'           => $datos['menor_correo'],
            'menor_telefono'         => $datos['menor_telefono'] ?? null,
            'menor_fecha_nacimiento' => $datos['menor_fecha_nacimiento'],
            'permiso_tutor_path'     => $datos['permiso_tutor_path'] ?? null,
            'cv_path'                => $datos['cv_path'] ?? null,
            'tipo_control'           => $datos['tipo_control'] ?? 'permiso_laboral',
            'estado'                 => $datos['estado'] ?? 'aprobado',
            'observaciones'          => $datos['observaciones'] ?? 'Registro de tutela verificado',
        ]);
    }

    public function obtenerPorMenor($usuarioMenId)
    {
        $sql = 'SELECT * FROM usuario_tutor WHERE usuario_men_id = :usuario_men_id ORDER BY fecha_registro DESC';
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute(['usuario_men_id' => (int) $usuarioMenId]);
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerTodos()
    {
        $sql = 'SELECT * FROM usuario_tutor ORDER BY fecha_registro DESC';
        return $this->conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizarEstado($id, $nuevoEstado)
    {
        $estadosValidos = ['aprobado', 'pendiente', 'denegado', 'revocado'];
        if (!in_array($nuevoEstado, $estadosValidos, true)) {
            return false;
        }
        $sql = 'UPDATE usuario_tutor SET estado = :estado WHERE id = :id';
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute([
            'estado' => $nuevoEstado,
            'id'     => (int) $id,
        ]);
    }
}
