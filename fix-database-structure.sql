-- ============================================
-- SCRIPT DE CORRECCIÓN DE ESTRUCTURA DE BD
-- Soluciona problemas de estructura para evitar errores JSON
-- ============================================

-- Verificar y agregar columna matches_played si no existe
ALTER TABLE players ADD COLUMN IF NOT EXISTS matches_played INT DEFAULT 0 AFTER name;

-- Actualizar valores NULL a 0 para evitar problemas
UPDATE players SET 
    matches_played = COALESCE(matches_played, 0),
    goals = COALESCE(goals, 0),
    assists = COALESCE(assists, 0),
    yellows = COALESCE(yellows, 0),
    reds = COALESCE(reds, 0)
WHERE 
    matches_played IS NULL OR 
    goals IS NULL OR 
    assists IS NULL OR 
    yellows IS NULL OR 
    reds IS NULL;

-- Verificar estructura de tabla de usuarios
SELECT COUNT(*) as usuarios_count FROM users WHERE username = 'admin';

-- Mostrar estructura actual de tablas
DESCRIBE players;
DESCRIBE users;

-- Verificar datos de ejemplo
SELECT COUNT(*) as total_jugadores FROM players;
SELECT name, goals, assists, yellows, reds, matches_played FROM players LIMIT 5;