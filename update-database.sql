-- IMPORTANTE: Ejecuta este SQL en phpMyAdmin ANTES de subir los archivos actualizados

-- Añadir columna de partidos jugados a la tabla players
ALTER TABLE players ADD COLUMN matches_played INT DEFAULT 0 AFTER name;

-- Opcional: Si ya tienes datos y quieres establecer valores por defecto
-- UPDATE players SET matches_played = 0 WHERE matches_played IS NULL;