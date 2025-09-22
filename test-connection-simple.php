<?php
header('Content-Type: application/json');

echo json_encode([
    'step' => 'Testing connection without config file',
    'host' => 'sql103.infinityfree.com',
    'user' => 'if0_39993495',
    'database' => 'if0_39993495_db_calicasas'
], JSON_UNESCAPED_UNICODE);

// Test básico sin funciones
try {
    $conn = new mysqli('sql103.infinityfree.com', 'if0_39993495', '9Hzc0DClqS', 'if0_39993495_db_calicasas');
    
    if ($conn->connect_error) {
        echo json_encode([
            'status' => 'FAILED',
            'error' => $conn->connect_error,
            'error_code' => $conn->connect_errno
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'status' => 'SUCCESS',
            'message' => 'Connection successful!',
            'server_info' => $conn->server_info
        ], JSON_UNESCAPED_UNICODE);
    }
    
    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        'status' => 'EXCEPTION',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>