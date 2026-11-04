-- Esquema de Frutería Online: tablas, fruta por defecto y admin

CREATE DATABASE IF NOT EXISTS fruteria CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fruteria;

CREATE TABLE usuarios (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  nombre_usuario VARCHAR(50)  NOT NULL UNIQUE,
  email          VARCHAR(120) NOT NULL UNIQUE,
  password_hash  VARCHAR(255) NOT NULL,
  rol            ENUM('usuario','admin') NOT NULL DEFAULT 'usuario',
  creado_en      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE frutas (
  id       INT AUTO_INCREMENT PRIMARY KEY,
  nombre   VARCHAR(100) NOT NULL,
  precio   DECIMAL(8,2) NOT NULL CHECK (precio >= 0),
  cantidad INT          NOT NULL DEFAULT 0 CHECK (cantidad >= 0),
  imagen   VARCHAR(500)
);

-- Datos iniciales
INSERT INTO frutas (nombre, precio, cantidad, imagen)
VALUES ('Fruta de la pasión', 3.50, 10, '');

-- Admin inicial: sustituir el hash por el resultado de
--   php -r "echo password_hash('TU_CONTRASEÑA', PASSWORD_DEFAULT);"
INSERT INTO usuarios (nombre_usuario, email, password_hash, rol)
VALUES ('admin', 'admin@fruteria.local', '<HASH_GENERADO_CON_PHP>', 'admin');
