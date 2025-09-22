<?php
// ============================================
// DIAGNÓSTICO COMPLETO DE CONEXIÓN BD
// ============================================

// Limpiar cualquier salida previa
ob_clean();
ob_start();

// Configurar headers para JSON
header('Content-Type: application/json');

echo json_encode([
    'debug' => 'Iniciando diagnóstico de conexión...'
], JSON_UNESCAPED_UNICODE);

// Mostrar configuración actual (SIN contraseña por seguridad)
require_once 'config.php';

$diagnostics = [
    'timestamp' => date('Y-m-d H:i:s'),
    'config_check' => [
        'DB_HOST' => DB_HOST,
        'DB_USER' => DB_USER,
        'DB_NAME' => DB_NAME,
        'DB_CHARSET' => DB_CHARSET,
        'password_length' => strlen(DB_PASS) . ' caracteres'
    ],
    'connection_tests' => []
];

// Test 1: Verificar que las constantes están definidas
$diagnostics['connection_tests']['constants_defined'] = [
    'DB_HOST_defined' => defined('DB_HOST'),
    'DB_USER_defined' => defined('DB_USER'),
    'DB_PASS_defined' => defined('DB_PASS'),
    'DB_NAME_defined' => defined('DB_NAME')
];

// Test 2: Intentar conexión básica
try {
    $diagnostics['connection_tests']['basic_connection'] = 'Intentando conexión...';
    
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        $diagnostics['connection_tests']['basic_connection'] = [
            'status' => 'FAILED',
            'error_code' => $conn->connect_errno,
            'error_message' => $conn->connect_error,
            'suggestion' => 'Verifica los datos de conexión en tu panel de InfinityFree'
        ];
    } else {
        $diagnostics['connection_tests']['basic_connection'] = [
            'status' => 'SUCCESS',
            'server_info' => $conn->server_info,
            'server_version' => $conn->server_version
        ];
        
        // Test 3: Verificar si existen las tablas
        $tables_check = [];
        
        $result = $conn->query("SHOW TABLES");
        if ($result) {
            $tables = [];
            while ($row = $result->fetch_array()) {
                $tables[] = $row[0];
            }
            $tables_check['existing_tables'] = $tables;
            $tables_check['users_exists'] = in_array('users', $tables);
            $tables_check['players_exists'] = in_array('players', $tables);
        } else {
            $tables_check['error'] = 'No se pudieron listar las tablas: ' . $conn->error;
        }
        
        $diagnostics['connection_tests']['tables_check'] = $tables_check;
        
        $conn->close();
    }
    
} catch (Exception $e) {
    $diagnostics['connection_tests']['basic_connection'] = [
        'status' => 'EXCEPTION',
        'error_message' => $e->getMessage(),
        'suggestion' => 'Error en la conexión a nivel de PHP'
    ];
}

// Test 4: Verificar función getDBConnection()
try {
    $diagnostics['connection_tests']['getDBConnection_test'] = 'Probando función getDBConnection()...';
    $conn = getDBConnection();
    $diagnostics['connection_tests']['getDBConnection_test'] = [
        'status' => 'SUCCESS',
        'message' => 'Función getDBConnection() funciona correctamente'
    ];
    $conn->close();
} catch (Exception $e) {
    $diagnostics['connection_tests']['getDBConnection_test'] = [
        'status' => 'FAILED',
        'error_message' => $e->getMessage()
    ];
}

// Limpiar buffer y enviar resultado
if (ob_get_level()) {
    ob_clean();
}

header('Content-Type: application/json');
echo json_encode($diagnostics, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
exit();
?>