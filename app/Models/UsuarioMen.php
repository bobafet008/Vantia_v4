<?php
require_once __DIR__ . '/Tutor.php';

class UsuarioMen
{
    private $conexion;
    private $modeloTutor;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->modeloTutor = new Tutor($conexion);
    }

    public function crear($datos)
    {
        $sql = 'INSERT INTO usuarios_men (
                    nombre, correo, contrasena, telefono, fecha_nacimiento, cv_path, permiso_tutor_path
                ) VALUES (
                    :nombre, :correo, :contrasena, :telefono, :fecha_nacimiento, :cv_path, :permiso_tutor_path
                )';
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute($datos);
    }

    public function crearConTutor($datosMenor, $datosTutor)
    {
        $this->crear($datosMenor);
        $usuarioMenId = (int) $this->conexion->lastInsertId();

        $this->modeloTutor->crear(
            $usuarioMenId,
            $datosTutor['nombre'] ?? '',
            $datosTutor['telefono'] ?? '',
            $datosTutor['relacion'] ?? 'Tutor legal',
            $datosTutor['ci'] ?? null,
            $datosTutor['correo'] ?? null
        );

        $tutorObj = $this->modeloTutor->obtenerPorUsuarioMenId($usuarioMenId);
        $tutorId = $tutorObj ? (int) $tutorObj['id'] : 0;

        if ($tutorId > 0) {
            try {
                $sqlControl = 'INSERT INTO usuario_tutor (
                    tutor_id, usuario_men_id, tutor_nombre, tutor_ci, tutor_correo, tutor_telefono, tutor_relacion,
                    menor_nombre, menor_correo, menor_telefono, menor_fecha_nacimiento, permiso_tutor_path, cv_path,
                    tipo_control, estado, observaciones
                ) VALUES (
                    :tutor_id, :usuario_men_id, :tutor_nombre, :tutor_ci, :tutor_correo, :tutor_telefono, :tutor_relacion,
                    :menor_nombre, :menor_correo, :menor_telefono, :menor_fecha_nacimiento, :permiso_tutor_path, :cv_path,
                    "permiso_laboral", "aprobado", "Registro de consentimiento y tutela verificado en la plataforma"
                )';
                $stmtControl = $this->conexion->prepare($sqlControl);
                $stmtControl->execute([
                    'tutor_id'               => $tutorId,
                    'usuario_men_id'         => $usuarioMenId,
                    'tutor_nombre'           => $datosTutor['nombre'] ?? '',
                    'tutor_ci'               => $datosTutor['ci'] ?? null,
                    'tutor_correo'           => $datosTutor['correo'] ?? null,
                    'tutor_telefono'         => $datosTutor['telefono'] ?? 0,
                    'tutor_relacion'         => $datosTutor['relacion'] ?? 'Tutor legal',
                    'menor_nombre'           => $datosMenor['nombre'] ?? '',
                    'menor_correo'           => $datosMenor['correo'] ?? '',
                    'menor_telefono'         => $datosMenor['telefono'] ?? null,
                    'menor_fecha_nacimiento' => $datosMenor['fecha_nacimiento'] ?? date('Y-m-d'),
                    'permiso_tutor_path'     => $datosMenor['permiso_tutor_path'] ?? null,
                    'cv_path'                => $datosMenor['cv_path'] ?? null,
                ]);
            } catch (Exception $e) {
                // Continuar si ocurre un fallo no crítico
                error_log('Error al registrar control_tutor: ' . $e->getMessage());
            }
        }

        return $usuarioMenId;
    }

    public function buscarPorCorreo($correo)
    {
        $sql = 'SELECT un.*, 
                       t.id AS tutor_id,
                       t.nombre AS tutor_nombre, 
                       t.ci AS tutor_ci, 
                       t.correo AS tutor_correo, 
                       t.telefono AS tutor_telefono, 
                       t.relacion AS tutor_relacion
                FROM usuarios_men un
                LEFT JOIN tutores t ON t.usuario_men_id = un.id
                WHERE un.correo = :correo 
                LIMIT 1';
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute(['correo' => $correo]);
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id)
    {
        $sql = 'SELECT un.*, 
                       t.id AS tutor_id,
                       t.nombre AS tutor_nombre, 
                       t.ci AS tutor_ci, 
                       t.correo AS tutor_correo, 
                       t.telefono AS tutor_telefono, 
                       t.relacion AS tutor_relacion
                FROM usuarios_men un
                LEFT JOIN tutores t ON t.usuario_men_id = un.id
                WHERE un.id = :id 
                LIMIT 1';
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute(['id' => (int) $id]);
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerTodos()
    {
        $sql = 'SELECT un.*, 
                       t.id AS tutor_id,
                       t.nombre AS tutor_nombre, 
                       t.ci AS tutor_ci, 
                       t.correo AS tutor_correo, 
                       t.telefono AS tutor_telefono, 
                       t.relacion AS tutor_relacion,
                       1 AS es_menor, 
                       "menor" AS candidato_tipo
                FROM usuarios_men un
                LEFT JOIN tutores t ON t.usuario_men_id = un.id
                ORDER BY un.fecha_registro DESC';
        return $this->conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function eliminar($id)
    {
        $sql = 'DELETE FROM usuarios_men WHERE id = :id';
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute(['id' => (int) $id]);
    }

    public function contar()
    {
        return (int) $this->conexion->query('SELECT COUNT(*) FROM usuarios_men')->fetchColumn();
    }
}
