<?php
// Limpiar cualquier salida previa
ob_clean();
ob_start();

// Manejar errores fatales
register_shutdown_function('handleFatalErrors');

function handleFatalErrors() {
    $error = error_get_last();
    if ($error !== NULL && $error['type'] === E_ERROR) {
        // Limpiar buffer
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Enviar respuesta JSON de error
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error fatal del servidor: ' . $error['message']
        ], JSON_UNESCAPED_UNICODE);
    }
}

// Iniciar sesión PHP
session_start();

// Incluir configuración de base de datos
require_once '../config.php';

// Configurar headers para API JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight requests (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Función para enviar respuesta JSON
function sendJSONResponse($data, $statusCode = 200) {
    // Limpiar cualquier output buffer
    if (ob_get_level()) {
        ob_clean();
    }
    
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

// Función para registrar intentos de login (seguridad básica)
function logLoginAttempt($username, $success, $ip) {
    error_log("Login attempt - User: $username, Success: " . ($success ? 'YES' : 'NO') . ", IP: $ip");
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Obtener datos del POST
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Validar que se enviaron datos
    if (!$data) {
        sendJSONResponse([
            'success' => false, 
            'error' => 'Datos inválidos o vacíos'
        ], 400);
    }
    
    // Obtener credenciales
    $username = trim($data['username'] ?? '');
    $password = $data['password'] ?? '';
    $userIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    // Validar que no estén vacíos
    if (empty($username) || empty($password)) {
        logLoginAttempt($username, false, $userIP);
        sendJSONResponse([
            'success' => false, 
            'error' => 'Usuario y contraseña son requeridos'
        ], 400);
    }
    
    try {
        // Conectar a la base de datos
        $conn = getDBConnection();
        
        // Buscar usuario en la base de datos
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ? LIMIT 1");
        if (!$stmt) {
            throw new Exception("Error en la consulta: " . $conn->error);
        }
        
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // IMPORTANTE: En producción usar password_verify()
            // Por ahora comparamos texto plano para simplicidad
            if ($password === $user['password']) {
                
                // Login exitoso - crear sesión
                session_regenerate_id(true); // Regenerar ID por seguridad
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();
                
                // Log del éxito
                logLoginAttempt($username, true, $userIP);
                
                // Respuesta exitosa
                sendJSONResponse([
                    'success' => true,
                    'message' => 'Login exitoso',
                    'user' => [
                        'username' => $user['username'],
                        'login_time' => date('Y-m-d H:i:s')
                    ]
                ]);
                
            } else {
                // Contraseña incorrecta
                logLoginAttempt($username, false, $userIP);
                sendJSONResponse([
                    'success' => false, 
                    'error' => 'Credenciales incorrectas'
                ], 401);
            }
        } else {
            // Usuario no encontrado
            logLoginAttempt($username, false, $userIP);
            sendJSONResponse([
                'success' => false, 
                'error' => 'Credenciales incorrectas'
            ], 401);
        }
        
        $stmt->close();
        $conn->close();
        
    } catch (Exception $e) {
        error_log("Error en login.php: " . $e->getMessage());
        
        // Verificar si el error es de conexión a BD
        if (strpos($e->getMessage(), 'conexión') !== false) {
            sendJSONResponse([
                'success' => false, 
                'error' => 'Error de conexión a la base de datos. Por favor, inténtalo más tarde.'
            ], 503);
        } else {
            sendJSONResponse([
                'success' => false, 
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

} 


else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    // Verificar si hay una sesión activa
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        sendJSONResponse([
            'success' => true,
            'logged_in' => true,
            'user' => [
                'username' => $_SESSION['username'] ?? 'admin',
                'login_time' => date('Y-m-d H:i:s', $_SESSION['login_time'] ?? time())
            ]
        ]);
    } else {
        sendJSONResponse([
            'success' => true,
            'logged_in' => false
        ]);
    }

}


else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    
    // Destruir sesión
    session_unset();
    session_destroy();
    
    sendJSONResponse([
        'success' => true,
        'message' => 'Sesión cerrada correctamente'
    ]);

}

// Método no permitido
else {
    sendJSONResponse([
        'success' => false, 
        'error' => 'Método HTTP no permitido'
    ], 405);
}
?>