-- ============================================
-- BASE DE DATOS PEÑA DE FÚTBOL - TEMPORADA 2026
-- Configuración completa para InfinityFree
-- Base de datos: if0_39993495_calicasas
-- ============================================

-- IMPORTANTE: En InfinityFree la base de datos ya está creada
-- Solo ejecuta desde CREATE TABLE en adelante

-- Crear tabla de usuarios (administradores)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Limpiar usuarios existentes (opcional)
DELETE FROM users WHERE username = 'admin';

-- Usuario administrador
-- Contraseña: calicacasas (¡CAMBIAR EN PRODUCCIÓN!)
INSERT INTO users (username, password) VALUES ('admin', 'calicacasas');

-- Crear tabla de jugadores
CREATE TABLE IF NOT EXISTS players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    matches_played INT DEFAULT 0,
    goals INT DEFAULT 0,
    assists INT DEFAULT 0,
    yellows INT DEFAULT 0,
    reds INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Limpiar jugadores existentes (opcional)
DELETE FROM players;

-- INSERTAR TODOS LOS JUGADORES DE LA TEMPORADA 2026
-- Árbitro: Juanxus
INSERT INTO players (name, matches_played, goals, assists, yellows, reds) VALUES
('Vicente', 0, 0, 0, 0, 0),
('Pablo', 0, 0, 0, 0, 0),
('Molero', 0, 0, 0, 0, 0),
('Carlos', 0, 0, 0, 0, 0),
('Raul', 0, 0, 0, 0, 0),
('Gonzalez', 0, 0, 0, 0, 0),
('Dani Gonzalez', 0, 0, 0, 0, 0),
('Javier (portero)', 0, 0, 0, 0, 0),
('Torres', 0, 0, 0, 0, 0),
('Horacio', 0, 0, 0, 0, 0),
('Rafa', 0, 0, 0, 0, 0),
('Álvaro', 0, 0, 0, 0, 0),
('Kiko', 0, 0, 0, 0, 0),
('Albertillo', 0, 0, 0, 0, 0),
('Ruben', 0, 0, 0, 0, 0),
('Xaxi', 0, 0, 0, 0, 0),
('Ivan', 0, 0, 0, 0, 0),
('Jose Angel', 0, 0, 0, 0, 0),
('Maikel', 0, 0, 0, 0, 0),
('Andrés', 0, 0, 0, 0, 0),
('Víctor (portero)', 0, 0, 0, 0, 0),
('Purpi', 0, 0, 0, 0, 0),
('Jose Alcala', 0, 0, 0, 0, 0),
('Pepe Polvillo', 0, 0, 0, 0, 0),
('Chini', 0, 0, 0, 0, 0),
('Diego', 0, 0, 0, 0, 0),
('Alexis', 0, 0, 0, 0, 0);

-- Índices para mejor rendimiento
CREATE INDEX IF NOT EXISTS idx_players_goals ON players(goals);
CREATE INDEX IF NOT EXISTS idx_players_assists ON players(assists);
CREATE INDEX IF NOT EXISTS idx_players_yellows ON players(yellows);
CREATE INDEX IF NOT EXISTS idx_players_reds ON players(reds);
CREATE INDEX IF NOT EXISTS idx_players_name ON players(name);

-- Verificar que todo esté correcto
SELECT COUNT(*) as total_usuarios FROM users;
SELECT COUNT(*) as total_jugadores FROM players;
SELECT 'Base de datos configurada correctamente' as status;