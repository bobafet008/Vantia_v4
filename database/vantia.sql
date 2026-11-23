CREATE DATABASE IF NOT EXISTS BS_VANTIA
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE BS_VANTIA;

CREATE TABLE IF NOT EXISTS usuarios_men (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(30) NOT NULL,
    correo VARCHAR(50) NOT NULL UNIQUE,
    contrasena VARCHAR(60) NOT NULL,
    telefono BIGINT DEFAULT NULL,
    fecha_nacimiento DATE NOT NULL,
    cv_path VARCHAR(80) DEFAULT NULL,
    permiso_tutor_path VARCHAR(80) DEFAULT NULL,
    baneado TINYINT(1) NOT NULL DEFAULT 0,
    baneado_hasta DATETIME DEFAULT NULL,
    motivo_baneo VARCHAR(60) DEFAULT NULL,
    intentos_fallidos INT NOT NULL DEFAULT 0,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS tutores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_men_id INT NOT NULL,
    nombre VARCHAR(30) NOT NULL,
    ci VARCHAR(15) DEFAULT NULL,
    correo VARCHAR(50) DEFAULT NULL,
    telefono BIGINT NOT NULL,
    relacion VARCHAR(25) NOT NULL DEFAULT 'Tutor legal',
    tipo_control ENUM('permiso_laboral', 'aprobacion_postulacion', 'consentimiento_general') NOT NULL DEFAULT 'permiso_laboral',
    estado ENUM('aprobado', 'pendiente', 'denegado', 'revocado') NOT NULL DEFAULT 'aprobado',
    observaciones VARCHAR(80) DEFAULT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_tutor_usuario_men (usuario_men_id),
    CONSTRAINT fk_tutores_usuario_men FOREIGN KEY (usuario_men_id) REFERENCES usuarios_men(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS usuarios_may (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(30) NOT NULL,
    correo VARCHAR(50) NOT NULL UNIQUE,
    contrasena VARCHAR(60) NOT NULL,
    tipo ENUM('empleado', 'admin') NOT NULL DEFAULT 'empleado',
    telefono BIGINT DEFAULT NULL,
    fecha_nacimiento DATE DEFAULT NULL,
    cv_path VARCHAR(80) DEFAULT NULL,
    baneado TINYINT(1) NOT NULL DEFAULT 0,
    baneado_hasta DATETIME DEFAULT NULL,
    motivo_baneo VARCHAR(60) DEFAULT NULL,
    intentos_fallidos INT NOT NULL DEFAULT 0,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS empresas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(30) NOT NULL,
    tipo_cuenta VARCHAR(20) NOT NULL DEFAULT 'empresa',
    nombre_empresa VARCHAR(50) GENERATED ALWAYS AS (nombre) VIRTUAL,
    nit BIGINT DEFAULT NULL,
    roe VARCHAR(80) NOT NULL,
    representante VARCHAR(40) NOT NULL,
    telefono BIGINT NOT NULL,
    correo VARCHAR(50) NOT NULL UNIQUE,
    contrasena VARCHAR(60) NOT NULL,
    descripcion TEXT DEFAULT NULL,
    verificada TINYINT(1) NOT NULL DEFAULT 1,
    baneado TINYINT(1) NOT NULL DEFAULT 0,
    baneado_hasta DATETIME DEFAULT NULL,
    motivo_baneo VARCHAR(60) DEFAULT NULL,
    intentos_fallidos INT NOT NULL DEFAULT 0,
    creada_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS emprendimientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(30) NOT NULL,
    nombre_emprendimiento VARCHAR(50) GENERATED ALWAYS AS (nombre) VIRTUAL,
    representante VARCHAR(40) NOT NULL,
    titular_nombre VARCHAR(40) GENERATED ALWAYS AS (representante) VIRTUAL,
    rubro VARCHAR(30) NOT NULL,
    ci BIGINT DEFAULT NULL,
    titular_ci BIGINT GENERATED ALWAYS AS (ci) VIRTUAL,
    telefono BIGINT NOT NULL,
    correo VARCHAR(50) NOT NULL UNIQUE,
    contrasena VARCHAR(60) NOT NULL,
    descripcion TEXT DEFAULT NULL,
    foto_puesto VARCHAR(80) NOT NULL DEFAULT 'uploads/roe/roe_default.png',
    logo_path VARCHAR(80) DEFAULT NULL,
    verificado TINYINT(1) NOT NULL DEFAULT 1,
    verificada TINYINT(1) GENERATED ALWAYS AS (verificado) VIRTUAL,
    baneado TINYINT(1) NOT NULL DEFAULT 0,
    baneado_hasta DATETIME DEFAULT NULL,
    motivo_baneo VARCHAR(60) DEFAULT NULL,
    intentos_fallidos INT NOT NULL DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    creada_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS empleos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    emprendimiento_id INT DEFAULT NULL,
    titulo VARCHAR(40) NOT NULL,
    categoria VARCHAR(30) NOT NULL,
    tipo ENUM('medio_tiempo', 'practicas', 'fines_semana', 'remoto') NOT NULL,
    descripcion TEXT NOT NULL,
    requisitos TEXT DEFAULT NULL,
    salario BIGINT DEFAULT NULL,
    ubicacion VARCHAR(60) DEFAULT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (empresa_id),
    INDEX (emprendimiento_id),
    CONSTRAINT fk_empleos_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    CONSTRAINT fk_empleos_emprendimiento FOREIGN KEY (emprendimiento_id) REFERENCES emprendimientos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS postulaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empleo_id INT NOT NULL,
    empresa_id INT NOT NULL,
    emprendimiento_id INT DEFAULT NULL,
    usuario_may_id INT DEFAULT NULL,
    usuario_men_id INT DEFAULT NULL,
    candidato_id INT NOT NULL,
    candidato_tipo ENUM('mayor', 'menor') NOT NULL DEFAULT 'mayor',
    mensaje TEXT DEFAULT NULL,
    estado ENUM('pendiente', 'revision', 'entrevista', 'contratado', 'rechazado') NOT NULL DEFAULT 'pendiente',
    fecha_postulacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (empleo_id),
    INDEX (empresa_id),
    INDEX (emprendimiento_id),
    INDEX (usuario_may_id),
    INDEX (usuario_men_id),
    INDEX (candidato_id),
    UNIQUE KEY uk_empleo_candidato (empleo_id, candidato_id, candidato_tipo),
    CONSTRAINT fk_postulacion_empleo FOREIGN KEY (empleo_id) REFERENCES empleos(id) ON DELETE CASCADE,
    CONSTRAINT fk_postulacion_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    CONSTRAINT fk_postulacion_emprendimiento FOREIGN KEY (emprendimiento_id) REFERENCES emprendimientos(id) ON DELETE CASCADE,
    CONSTRAINT fk_postulacion_usuario_may FOREIGN KEY (usuario_may_id) REFERENCES usuarios_may(id) ON DELETE CASCADE,
    CONSTRAINT fk_postulacion_usuario_men FOREIGN KEY (usuario_men_id) REFERENCES usuarios_men(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS calificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    emprendimiento_id INT DEFAULT NULL,
    usuario_may_id INT DEFAULT NULL,
    usuario_men_id INT DEFAULT NULL,
    candidato_id INT NOT NULL,
    candidato_tipo ENUM('mayor', 'menor') NOT NULL DEFAULT 'mayor',
    puntuacion TINYINT NOT NULL CHECK (puntuacion BETWEEN 0 AND 5),
    comentario TEXT DEFAULT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_calif_empresa (empresa_id),
    INDEX idx_calif_emprendimiento (emprendimiento_id),
    INDEX (usuario_may_id),
    INDEX (usuario_men_id),
    UNIQUE KEY uk_calif_empresa_candidato (empresa_id, candidato_id, candidato_tipo),
    CONSTRAINT fk_calif_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    CONSTRAINT fk_calif_emprendimiento FOREIGN KEY (emprendimiento_id) REFERENCES emprendimientos(id) ON DELETE CASCADE,
    CONSTRAINT fk_calif_usuario_may FOREIGN KEY (usuario_may_id) REFERENCES usuarios_may(id) ON DELETE CASCADE,
    CONSTRAINT fk_calif_usuario_men FOREIGN KEY (usuario_men_id) REFERENCES usuarios_men(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS usuarios_admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(30) NOT NULL,
    correo VARCHAR(50) NOT NULL UNIQUE,
    contrasena VARCHAR(60) NOT NULL,
    telefono BIGINT DEFAULT NULL,
    cargo VARCHAR(35) NOT NULL DEFAULT 'Administrador General',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO usuarios_admin (nombre, correo, contrasena, telefono, cargo, activo)
VALUES (
    'Administrador General',
    'admin@vantia.com',
    '$2y$10$Q58KCi7JnxX7ugFBKuizP.Y.CUdsUbgpJ6RJ7lC63Doy8Kmbv5Ayi',
    70000000,
    'Administrador General',
    1
)
ON DUPLICATE KEY UPDATE activo = 1;

CREATE TABLE IF NOT EXISTS mensajes_soporte (
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
    INDEX (emprendimiento_id),
    CONSTRAINT fk_soporte_usuario_may FOREIGN KEY (usuario_may_id) REFERENCES usuarios_may(id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_soporte_usuario_men FOREIGN KEY (usuario_men_id) REFERENCES usuarios_men(id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_soporte_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_soporte_emprendimiento FOREIGN KEY (emprendimiento_id) REFERENCES emprendimientos(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS registros_admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT DEFAULT NULL,
    usuario_may_id INT DEFAULT NULL,
    usuario_men_id INT DEFAULT NULL,
    empresa_id INT DEFAULT NULL,
    emprendimiento_id INT DEFAULT NULL,
    mensaje_soporte_id INT DEFAULT NULL,
    tipo_evento ENUM('reporte', 'baneo', 'desbaneo', 'nuevo_usuario', 'bloqueo_correo', 'verificacion', 'accion_admin', 'soporte') NOT NULL,
    titulo VARCHAR(50) NOT NULL,
    descripcion VARCHAR(80) NOT NULL,
    usuario_afectado VARCHAR(50) DEFAULT NULL,
    ip_origen VARCHAR(45) DEFAULT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (admin_id),
    INDEX (usuario_may_id),
    INDEX (usuario_men_id),
    INDEX (empresa_id),
    INDEX (emprendimiento_id),
    INDEX (mensaje_soporte_id),
    INDEX (tipo_evento),
    INDEX (fecha_registro),
    CONSTRAINT fk_registros_admin_admin FOREIGN KEY (admin_id) REFERENCES usuarios_admin(id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_registros_admin_usuario_may FOREIGN KEY (usuario_may_id) REFERENCES usuarios_may(id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_registros_admin_usuario_men FOREIGN KEY (usuario_men_id) REFERENCES usuarios_men(id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_registros_admin_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_registros_admin_emprendimiento FOREIGN KEY (emprendimiento_id) REFERENCES emprendimientos(id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_registros_admin_soporte FOREIGN KEY (mensaje_soporte_id) REFERENCES mensajes_soporte(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
