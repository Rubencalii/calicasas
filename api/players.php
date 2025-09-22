<?php

// Iniciar sesión PHP
session_start();

// Incluir configuración de base de datos
require_once '../config.php';

// Configurar headers para API JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight requests (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


function sendJSONResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

function isAuthenticated() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function requireAuthentication() {
    if (!isAuthenticated()) {
        sendJSONResponse([
            'success' => false,
            'error' => 'Acceso denegado. Debes iniciar sesión como administrador.'
        ], 401);
    }
}

function calculateOffensivePoints($goals, $assists) {
    return ($goals * 3) + ($assists * 2);
}

function calculatePenaltyPoints($yellows, $reds) {
    return ($yellows * -1) + ($reds * -3);
}

function validatePlayerStats($matches_played, $goals, $assists, $yellows, $reds) {
    return is_numeric($matches_played) && $matches_played >= 0 &&
           is_numeric($goals) && $goals >= 0 &&
           is_numeric($assists) && $assists >= 0 &&
           is_numeric($yellows) && $yellows >= 0 &&
           is_numeric($reds) && $reds >= 0;
}


$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];


if ($action === 'list' && $method === 'GET') {
    
    try {
        $conn = getDBConnection();
        
        // Consulta con cálculos de puntos
        $sql = "SELECT 
                    id,
                    name,
                    goals,
                    assists,
                    yellows,
                    reds,
                    created_at,
                    updated_at
                FROM players 
                ORDER BY name ASC";
        
        $result = $conn->query($sql);
        
        if (!$result) {
            throw new Exception("Error en la consulta: " . $conn->error);
        }
        
        $players = [];
        while ($row = $result->fetch_assoc()) {
            // Calcular puntos en PHP
            $row['offensive_points'] = calculateOffensivePoints($row['goals'], $row['assists']);
            $row['penalty_points'] = calculatePenaltyPoints($row['yellows'], $row['reds']);
            
            $players[] = $row;
        }
        
        $conn->close();
        
        sendJSONResponse([
            'success' => true,
            'count' => count($players),
            'players' => $players,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        error_log("Error en listado de jugadores: " . $e->getMessage());
        
        // Verificar si el error es de conexión a BD
        if (strpos($e->getMessage(), 'conexión') !== false) {
            sendJSONResponse([
                'success' => false,
                'error' => 'Error de conexión a la base de datos. Por favor, inténtalo más tarde.'
            ], 503);
        } else {
            sendJSONResponse([
                'success' => false,
                'error' => 'Error al obtener la lista de jugadores'
            ], 500);
        }
    }
}

// ============================================
// ENDPOINT: CREAR NUEVO JUGADOR
// POST /api/players.php?action=create
// ============================================

else if ($action === 'create' && $method === 'POST') {
    
    requireAuthentication();
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || empty(trim($data['name'] ?? ''))) {
        sendJSONResponse([
            'success' => false,
            'error' => 'El nombre del jugador es requerido'
        ], 400);
    }
    
    try {
        $conn = getDBConnection();
        
        $name = trim($data['name']);
        
        // Verificar que no existe ya un jugador con ese nombre
        $checkStmt = $conn->prepare("SELECT id FROM players WHERE name = ? LIMIT 1");
        $checkStmt->bind_param("s", $name);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            sendJSONResponse([
                'success' => false,
                'error' => 'Ya existe un jugador con ese nombre'
            ], 409);
        }
        
        // Crear nuevo jugador
        $stmt = $conn->prepare("INSERT INTO players (name, goals, assists, yellows, reds) VALUES (?, 0, 0, 0, 0)");
        if (!$stmt) {
            throw new Exception("Error preparando consulta: " . $conn->error);
        }
        
        $stmt->bind_param("s", $name);
        
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando consulta: " . $stmt->error);
        }
        
        $newPlayerId = $stmt->insert_id;
        
        $stmt->close();
        $checkStmt->close();
        $conn->close();
        
        sendJSONResponse([
            'success' => true,
            'message' => 'Jugador creado exitosamente',
            'player' => [
                'id' => $newPlayerId,
                'name' => $name,
                'goals' => 0,
                'assists' => 0,
                'yellows' => 0,
                'reds' => 0
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Error creando jugador: " . $e->getMessage());
        
        // Verificar si el error es de conexión a BD
        if (strpos($e->getMessage(), 'conexión') !== false) {
            sendJSONResponse([
                'success' => false,
                'error' => 'Error de conexión a la base de datos. Por favor, inténtalo más tarde.'
            ], 503);
        } else {
            sendJSONResponse([
                'success' => false,
                'error' => 'Error al crear el jugador'
            ], 500);
        }
    }
}



else if ($action === 'update' && $method === 'POST') {
    
    requireAuthentication();
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        sendJSONResponse([
            'success' => false,
            'error' => 'Datos inválidos'
        ], 400);
    }
    
    $playerId = intval($data['id'] ?? 0);
    $matches_played = intval($data['matches_played'] ?? 0);
    $goals = intval($data['goals'] ?? 0);
    $assists = intval($data['assists'] ?? 0);
    $yellows = intval($data['yellows'] ?? 0);
    $reds = intval($data['reds'] ?? 0);
    
    // Validaciones
    if ($playerId <= 0) {
        sendJSONResponse([
            'success' => false,
            'error' => 'ID de jugador inválido'
        ], 400);
    }
    
    if (!validatePlayerStats($matches_played, $goals, $assists, $yellows, $reds)) {
        sendJSONResponse([
            'success' => false,
            'error' => 'Las estadísticas deben ser números positivos'
        ], 400);
    }
    
    try {
        $conn = getDBConnection();
        
        // Verificar que el jugador existe
        $checkStmt = $conn->prepare("SELECT name FROM players WHERE id = ? LIMIT 1");
        $checkStmt->bind_param("i", $playerId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows === 0) {
            sendJSONResponse([
                'success' => false,
                'error' => 'Jugador no encontrado'
            ], 404);
        }
        
        $playerName = $checkResult->fetch_assoc()['name'];
        
        // Actualizar estadísticas
        $stmt = $conn->prepare("UPDATE players SET matches_played = ?, goals = ?, assists = ?, yellows = ?, reds = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Error preparando consulta: " . $conn->error);
        }
        
        $stmt->bind_param("iiiiii", $matches_played, $goals, $assists, $yellows, $reds, $playerId);
        
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando consulta: " . $stmt->error);
        }
        
        $stmt->close();
        $checkStmt->close();
        $conn->close();
        
        sendJSONResponse([
            'success' => true,
            'message' => "Estadísticas de $playerName actualizadas exitosamente",
            'player' => [
                'id' => $playerId,
                'name' => $playerName,
                'matches_played' => $matches_played,
                'goals' => $goals,
                'assists' => $assists,
                'yellows' => $yellows,
                'reds' => $reds,
                'offensive_points' => calculateOffensivePoints($goals, $assists),
                'penalty_points' => calculatePenaltyPoints($yellows, $reds)
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Error actualizando jugador: " . $e->getMessage());
        
        // Verificar si el error es de conexión a BD
        if (strpos($e->getMessage(), 'conexión') !== false) {
            sendJSONResponse([
                'success' => false,
                'error' => 'Error de conexión a la base de datos. Por favor, inténtalo más tarde.'
            ], 503);
        } else {
            sendJSONResponse([
                'success' => false,
                'error' => 'Error al actualizar las estadísticas'
            ], 500);
        }
    }
}



else if ($action === 'reset' && $method === 'POST') {
    
    requireAuthentication();
    
    try {
        $conn = getDBConnection();
        
        // Reiniciar todas las estadísticas a 0
        $sql = "UPDATE players SET matches_played = 0, goals = 0, assists = 0, yellows = 0, reds = 0, updated_at = CURRENT_TIMESTAMP";
        
        if (!$conn->query($sql)) {
            throw new Exception("Error ejecutando reset: " . $conn->error);
        }
        
        $affectedRows = $conn->affected_rows;
        $conn->close();
        
        sendJSONResponse([
            'success' => true,
            'message' => 'Todas las estadísticas han sido reiniciadas',
            'affected_players' => $affectedRows,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        error_log("Error reiniciando estadísticas: " . $e->getMessage());
        
        // Verificar si el error es de conexión a BD
        if (strpos($e->getMessage(), 'conexión') !== false) {
            sendJSONResponse([
                'success' => false,
                'error' => 'Error de conexión a la base de datos. Por favor, inténtalo más tarde.'
            ], 503);
        } else {
            sendJSONResponse([
                'success' => false,
                'error' => 'Error al reiniciar las estadísticas'
            ], 500);
        }
    }
}

else if ($action === 'delete' && $method === 'DELETE') {
    
    requireAuthentication();
    
    $playerId = intval($_GET['id'] ?? 0);
    
    if ($playerId <= 0) {
        sendJSONResponse([
            'success' => false,
            'error' => 'ID de jugador inválido'
        ], 400);
    }
    
    try {
        $conn = getDBConnection();
        
        // Obtener nombre del jugador antes de eliminarlo
        $checkStmt = $conn->prepare("SELECT name FROM players WHERE id = ? LIMIT 1");
        $checkStmt->bind_param("i", $playerId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows === 0) {
            sendJSONResponse([
                'success' => false,
                'error' => 'Jugador no encontrado'
            ], 404);
        }
        
        $playerName = $checkResult->fetch_assoc()['name'];
        
        // Eliminar jugador
        $stmt = $conn->prepare("DELETE FROM players WHERE id = ?");
        $stmt->bind_param("i", $playerId);
        
        if (!$stmt->execute()) {
            throw new Exception("Error eliminando jugador: " . $stmt->error);
        }
        
        $stmt->close();
        $checkStmt->close();
        $conn->close();
        
        sendJSONResponse([
            'success' => true,
            'message' => "Jugador $playerName eliminado exitosamente"
        ]);
        
    } catch (Exception $e) {
        error_log("Error eliminando jugador: " . $e->getMessage());
        
        // Verificar si el error es de conexión a BD
        if (strpos($e->getMessage(), 'conexión') !== false) {
            sendJSONResponse([
                'success' => false,
                'error' => 'Error de conexión a la base de datos. Por favor, inténtalo más tarde.'
            ], 503);
        } else {
            sendJSONResponse([
                'success' => false,
                'error' => 'Error al eliminar el jugador'
            ], 500);
        }
    }
}

else {
    sendJSONResponse([
        'success' => false,
        'error' => 'Acción no válida o método HTTP no permitido',
        'available_actions' => ['list', 'create', 'update', 'reset', 'delete'],
        'your_request' => [
            'action' => $action,
            'method' => $method
        ]
    ], 400);
}

?>