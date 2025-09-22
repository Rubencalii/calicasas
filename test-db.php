<?php
// Limpiar cualquier salida previa
ob_clean();
ob_start();

// Configurar headers para API JSON
header('Content-Type: application/json');

// Incluir configuración de base de datos
require_once 'config.php';

try {
    $conn = getDBConnection();
    
    // Verificar si existe la tabla players
    $result = $conn->query("SHOW TABLES LIKE 'players'");
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'error' => 'La tabla players no existe',
            'suggestion' => 'Ejecuta el archivo db.sql para crear la tabla'
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    // Verificar la estructura de la tabla
    $structure = $conn->query("DESCRIBE players");
    $columns = [];
    while ($row = $structure->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    
    // Verificar si existe la columna matches_played
    if (!in_array('matches_played', $columns)) {
        // Agregar la columna si no existe
        $conn->query("ALTER TABLE players ADD COLUMN matches_played INT DEFAULT 0 AFTER name");
    }
    
    // Hacer una consulta simple
    $result = $conn->query("SELECT COUNT(*) as total FROM players");
    $count = $result->fetch_assoc()['total'];
    
    // Obtener algunos jugadores de ejemplo
    $result = $conn->query("SELECT id, name, COALESCE(matches_played, 0) as matches_played, COALESCE(goals, 0) as goals FROM players LIMIT 3");
    $samplePlayers = [];
    while ($row = $result->fetch_assoc()) {
        $samplePlayers[] = $row;
    }
    
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Base de datos conectada correctamente',
        'total_players' => $count,
        'table_columns' => $columns,
        'sample_players' => $samplePlayers
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'type' => 'database_error'
    ], JSON_UNESCAPED_UNICODE);
}
?>