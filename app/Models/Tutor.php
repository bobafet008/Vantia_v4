<?php
class Tutor
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->asegurarColumnas();
    }

    private function asegurarColumnas()
    {
        try {
            $colCi = $this->conexion->query("SHOW COLUMNS FROM tutores LIKE 'ci'")->fetch();
            if (!$colCi) {
                $this->conexion->exec("ALTER TABLE tutores ADD COLUMN ci VARCHAR(30) NULL AFTER nombre");
            }
            $colCorreo = $this->conexion->query("SHOW COLUMNS FROM tutores LIKE 'correo'")->fetch();
            if (!$colCorreo) {
                $this->conexion->exec("ALTER TABLE tutores ADD COLUMN correo VARCHAR(150) NULL AFTER ci");
            }
        } catch (Exception $e) {
            // Continuar si no se tienen permisos directos de ALTER
        }
    }

    public function crear($usuarioMenId, $nombre, $telefono, $relacion = 'Tutor legal', $ci = null, $correo = null)
    {
        $this->asegurarColumnas();
        try {
            $sql = 'INSERT INTO tutores (usuario_men_id, nombre, ci, correo, telefono, relacion)
                    VALUES (:usuario_men_id, :nombre, :ci, :correo, :telefono, :relacion)
                    ON DUPLICATE KEY UPDATE 
                        nombre = VALUES(nombre), 
                        ci = VALUES(ci),
                        correo = VALUES(correo),
                        telefono = VALUES(telefono), 
                        relacion = VALUES(relacion)';
            $consulta = $this->conexion->prepare($sql);
            return $consulta->execute([
                'usuario_men_id' => (int) $usuarioMenId,
                'nombre'         => $nombre,
                'ci'             => $ci ?: null,
                'correo'         => $correo ?: null,
                'telefono'       => $telefono,
                'relacion'       => $relacion ?: 'Tutor legal',
            ]);
        } catch (PDOException $e) {
            try {
                $sql = 'INSERT INTO tutores (usuario_men_id, nombre, ci, telefono, relacion)
                        VALUES (:usuario_men_id, :nombre, :ci, :telefono, :relacion)
                        ON DUPLICATE KEY UPDATE 
                            nombre = VALUES(nombre), 
                            ci = VALUES(ci), 
                            telefono = VALUES(telefono), 
                            relacion = VALUES(relacion)';
                $consulta = $this->conexion->prepare($sql);
                return $consulta->execute([
                    'usuario_men_id' => (int) $usuarioMenId,
                    'nombre'         => $nombre,
                    'ci'             => $ci ?: null,
                    'telefono'       => $telefono,
                    'relacion'       => $relacion ?: 'Tutor legal',
                ]);
            } catch (PDOException $e2) {
                $sql = 'INSERT INTO tutores (usuario_men_id, nombre, telefono, relacion)
                        VALUES (:usuario_men_id, :nombre, :telefono, :relacion)
                        ON DUPLICATE KEY UPDATE 
                            nombre = VALUES(nombre), 
                            telefono = VALUES(telefono), 
                            relacion = VALUES(relacion)';
                $consulta = $this->conexion->prepare($sql);
                return $consulta->execute([
                    'usuario_men_id' => (int) $usuarioMenId,
                    'nombre'         => $nombre,
                    'telefono'       => $telefono,
                    'relacion'       => $relacion ?: 'Tutor legal',
                ]);
            }
        }
    }

    public function obtenerPorUsuarioMenId($usuarioMenId)
    {
        $sql = 'SELECT * FROM tutores WHERE usuario_men_id = :usuario_men_id LIMIT 1';
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute(['usuario_men_id' => (int) $usuarioMenId]);
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerTodos()
    {
        $sql = 'SELECT t.*, 
                       un.nombre AS menor_nombre, 
                       un.correo AS menor_correo, 
                       un.telefono AS menor_telefono, 
                       un.fecha_nacimiento,
                       un.cv_path,
                       un.permiso_tutor_path
                FROM tutores t
                INNER JOIN usuarios_men un ON un.id = t.usuario_men_id
                ORDER BY t.creado_en DESC';
        return $this->conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function eliminar($id)
    {
        $sql = 'DELETE FROM tutores WHERE id = :id';
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute(['id' => (int) $id]);
    }

    public function contar()
    {
        return (int) $this->conexion->query('SELECT COUNT(*) FROM tutores')->fetchColumn();
    }
}
