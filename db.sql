-- ============================================
-- BASE DE DATOS PARA PEÑA DE FÚTBOL - TEMPORADA 2026
-- Sistema de gestión de estadísticas de jugadores
-- ============================================

-- Crear tabla de usuarios (administradores)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Usuario administrador por defecto
-- Contraseña: calicacasas (¡CAMBIAR EN PRODUCCIÓN!)
INSERT INTO users (username, password) VALUES ('admin', 'calicacasas');

-- Crear tabla de jugadores
CREATE TABLE players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    goals INT DEFAULT 0,
    assists INT DEFAULT 0,
    yellows INT DEFAULT 0,
    reds INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- INSERTAR TODOS LOS JUGADORES DE LA TEMPORADA 2026
-- Árbitro: Juanxus
INSERT INTO players (name, goals, assists, yellows, reds) VALUES
('Vicente', 0, 0, 0, 0),
('Pablo', 0, 0, 0, 0),
('Molero', 0, 0, 0, 0),
('Carlos', 0, 0, 0, 0),
('Raul', 0, 0, 0, 0),
('Gonzalez', 0, 0, 0, 0),
('Dani Gonzalez', 0, 0, 0, 0),
('Javier (portero)', 0, 0, 0, 0),
('Torres', 0, 0, 0, 0),
('Horacio', 0, 0, 0, 0),
('Rafa', 0, 0, 0, 0),
('Álvaro', 0, 0, 0, 0),
('Kiko', 0, 0, 0, 0),
('Albertillo', 0, 0, 0, 0),
('Ruben', 0, 0, 0, 0),
('Xaxi', 0, 0, 0, 0),
('Ivan', 0, 0, 0, 0),
('Jose Angel', 0, 0, 0, 0),
('Maikel', 0, 0, 0, 0),
('Andrés', 0, 0, 0, 0),
('Víctor (portero)', 0, 0, 0, 0),
('Purpi', 0, 0, 0, 0),
('Jose Alcala', 0, 0, 0, 0),
('Pepe Polvillo', 0, 0, 0, 0),
('Chini', 0, 0, 0, 0),
('Diego', 0, 0, 0, 0),
('Alexis', 0, 0, 0, 0);

-- Índices para mejor rendimiento
CREATE INDEX idx_players_goals ON players(goals);
CREATE INDEX idx_players_assists ON players(assists);
CREATE INDEX idx_players_yellows ON players(yellows);
CREATE INDEX idx_players_reds ON players(reds);

-- ============================================
-- NOTAS IMPORTANTES:
-- 1. Cambiar la contraseña del admin antes de subir a producción
-- 2. Usar password_hash() en PHP para contraseñas seguras
-- 3. El árbitro Juanxus no está incluido como jugador
-- 4. Todos los jugadores empiezan con estadísticas en 0
-- ============================================