CREATE DATABASE IF NOT EXISTS incidencias_db;

USE incidencias_db;

CREATE TABLE IF NOT EXISTS incidencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    prioridad ENUM('baja', 'media', 'alta') NOT NULL DEFAULT 'media',
    estado ENUM('abierta', 'en_proceso', 'resuelta')
        NOT NULL DEFAULT 'abierta',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO incidencias (titulo, descripcion, prioridad, estado)
VALUES
(
    'Equipo no enciende',
    'La computadora del laboratorio no enciende al presionar el botón de encendido.',
    'alta',
    'abierta'
),
(
    'Problema de conexión',
    'Una computadora presenta problemas para conectarse a la red.',
    'media',
    'en_proceso'
),
(
    'Teclado defectuoso',
    'Algunas teclas del teclado no responden correctamente.',
    'baja',
    'resuelta'
);
