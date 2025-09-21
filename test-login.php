<?php
// Archivo de diagnóstico para el problema de login
require_once 'config.php';

echo "<h2>🔍 Diagnóstico de Login</h2>";

try {
    $conn = getDBConnection();
    echo "✅ Conexión a BD exitosa<br><br>";
    
    // Buscar usuario admin
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $username = 'admin';
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo "✅ Usuario encontrado:<br>";
        echo "ID: " . $user['id'] . "<br>";
        echo "Username: '" . $user['username'] . "'<br>";
        echo "Password: '" . $user['password'] . "'<br>";
        echo "Longitud password: " . strlen($user['password']) . " caracteres<br><br>";
        
        // Probar comparación exacta
        $test_password = 'calicacasas';
        echo "🧪 Probando comparación:<br>";
        echo "Test password: '" . $test_password . "'<br>";
        echo "BD password: '" . $user['password'] . "'<br>";
        
        if ($test_password === $user['password']) {
            echo "✅ ¡Las contraseñas coinciden exactamente!<br>";
        } else {
            echo "❌ Las contraseñas NO coinciden<br>";
            echo "Diferencia detectada en caracteres<br>";
            
            // Mostrar caracteres individuales
            echo "<br>🔍 Análisis carácter por carácter:<br>";
            for ($i = 0; $i < max(strlen($test_password), strlen($user['password'])); $i++) {
                $char1 = isset($test_password[$i]) ? $test_password[$i] : 'NULL';
                $char2 = isset($user['password'][$i]) ? $user['password'][$i] : 'NULL';
                $match = ($char1 === $char2) ? '✅' : '❌';
                echo "Pos $i: '$char1' vs '$char2' $match<br>";
            }
        }
        
    } else {
        echo "❌ Usuario admin NO encontrado en la base de datos<br>";
        echo "Usuarios existentes:<br>";
        
        $allUsers = $conn->query("SELECT username FROM users");
        while ($row = $allUsers->fetch_assoc()) {
            echo "- " . $row['username'] . "<br>";
        }
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>