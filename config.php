<?php

// Host de MySQL (típicamente algo como sql###.infinityfree.com)
define('DB_HOST', 'sql103.infinityfree.com');

// Nombre de usuario MySQL (formato: if0_XXXXXXXX)
define('DB_USER', 'if0_39993495');

// Contraseña de la base de datos MySQL
define('DB_PASS', '9Hzc0DClqS');

// Nombre de la base de datos (formato: if0_XXXXXXXX_db_calicasas)
define('DB_NAME', 'if0_39993495_db_calicasas');

// Configuración adicional
define('DB_CHARSET', 'utf8mb4');

// Función para crear conexión MySQL con manejo de errores
function getDBConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Verificar conexión
        if ($conn->connect_error) {
            error_log("Error de conexión MySQL: " . $conn->connect_error);
            throw new Exception("Error de conexión a la base de datos: " . $conn->connect_error);
        }
        
        // Establecer charset UTF-8
        $conn->set_charset(DB_CHARSET);
        
        return $conn;
    } catch (Exception $e) {
        error_log("Excepción en conexión MySQL: " . $e->getMessage());
        throw new Exception("Error de conexión a la base de datos: " . $e->getMessage());
    }
}

// Configuración de zona horaria
date_default_timezone_set('Europe/Madrid');

// Configuración de sesión más segura
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

?>