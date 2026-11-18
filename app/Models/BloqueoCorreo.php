<?php
class BloqueoCorreo
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
            $sql = "CREATE TABLE IF NOT EXISTS bloqueos_correo (
                id INT AUTO_INCREMENT PRIMARY KEY,
                correo VARCHAR(150) NOT NULL UNIQUE,
                intentos INT NOT NULL DEFAULT 0,
                bloqueado TINYINT(1) NOT NULL DEFAULT 0,
                bloqueado_en DATETIME DEFAULT NULL,
                ultimo_intento DATETIME DEFAULT NULL,
                creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            $this->conexion->exec($sql);
        } catch (Exception $e) {
            // Silencioso
        }
    }

    public function normalizarCorreo($correo)
    {
        return strtolower(trim($correo));
    }

    public function obtenerPorCorreo($correo)
    {
        $correoNorm = $this->normalizarCorreo($correo);
        $sql = 'SELECT * FROM bloqueos_correo WHERE correo = :correo LIMIT 1';
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute(['correo' => $correoNorm]);
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    public function estaBloqueado($correo)
    {
        $registro = $this->obtenerPorCorreo($correo);
        if (!$registro) {
            return false;
        }
        return (int) $registro['bloqueado'] === 1 || (int) $registro['intentos'] >= 3;
    }

    public function registrarFallo($correo)
    {
        $correoNorm = $this->normalizarCorreo($correo);
        if ($correoNorm === '') {
            return ['intentos' => 0, 'bloqueado' => false, 'restantes' => 3];
        }

        $sql = 'INSERT INTO bloqueos_correo (correo, intentos, ultimo_intento) 
                VALUES (:correo, 1, NOW()) 
                ON DUPLICATE KEY UPDATE intentos = intentos + 1, ultimo_intento = NOW()';
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute(['correo' => $correoNorm]);

        $registro = $this->obtenerPorCorreo($correoNorm);
        $intentos = (int) ($registro['intentos'] ?? 1);

        if ($intentos >= 3) {
            $sqlBloq = 'UPDATE bloqueos_correo SET bloqueado = 1, bloqueado_en = NOW() WHERE correo = :correo';
            $stmtBloq = $this->conexion->prepare($sqlBloq);
            $stmtBloq->execute(['correo' => $correoNorm]);
            return ['intentos' => $intentos, 'bloqueado' => true, 'restantes' => 0];
        }

        return ['intentos' => $intentos, 'bloqueado' => false, 'restantes' => max(0, 3 - $intentos)];
    }

    public function resetear($correo)
    {
        $correoNorm = $this->normalizarCorreo($correo);
        $sql = 'UPDATE bloqueos_correo SET intentos = 0, bloqueado = 0, bloqueado_en = NULL WHERE correo = :correo';
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute(['correo' => $correoNorm]);
    }

    public function bloquear($correo)
    {
        $correoNorm = $this->normalizarCorreo($correo);
        if ($correoNorm === '') return false;

        $sql = 'INSERT INTO bloqueos_correo (correo, intentos, bloqueado, bloqueado_en, ultimo_intento) 
                VALUES (:correo, 3, 1, NOW(), NOW()) 
                ON DUPLICATE KEY UPDATE intentos = 3, bloqueado = 1, bloqueado_en = NOW(), ultimo_intento = NOW()';
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute(['correo' => $correoNorm]);
    }

    public function desbloquear($correo)
    {
        $correoNorm = $this->normalizarCorreo($correo);
        $sql = 'UPDATE bloqueos_correo SET intentos = 0, bloqueado = 0, bloqueado_en = NULL WHERE correo = :correo';
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute(['correo' => $correoNorm]);
    }

    public function obtenerTodos()
    {
        $sql = 'SELECT * FROM bloqueos_correo WHERE intentos > 0 OR bloqueado = 1 ORDER BY bloqueado DESC, ultimo_intento DESC';
        return $this->conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarBloqueados()
    {
        return (int) $this->conexion->query('SELECT COUNT(*) FROM bloqueos_correo WHERE bloqueado = 1')->fetchColumn();
    }

    public function obtenerMapaBloqueados()
    {
        $todos = $this->obtenerTodos();
        $mapa = [];
        foreach ($todos as $item) {
            $mapa[$item['correo']] = $item;
        }
        return $mapa;
    }
}
