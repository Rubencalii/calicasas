<?php


require_once 'config.php';

try {
    $conn = getDBConnection();
    echo "<h2>🔧 Solucionando problema de login - NUEVO DOMINIO</h2>";
    
    // Limpiar usuario admin
    echo "1. Borrando usuario admin existente...<br>";
    $conn->query("DELETE FROM users WHERE username = 'admin'");
    
    // Crear usuario admin limpio
    echo "2. Creando usuario admin limpio...<br>";
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $username = 'admin';
    $password = 'calicacasas';
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    
    // Verificar
    echo "3. Verificando usuario creado...<br>";
    $result = $conn->query("SELECT username, password FROM users WHERE username = 'admin'");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo "✅ Usuario admin creado correctamente:<br>";
        echo "- Username: " . $user['username'] . "<br>";
        echo "- Password: " . $user['password'] . "<br>";
    }
    
    echo "<br>🎯 <strong>¡Problema solucionado con NUEVO DOMINIO!</strong><br>";
    echo "<br>📝 <strong>Nuevo dominio configurado correctamente</strong><br>";
    echo "<br>⚠️ <strong>IMPORTANTE: Borra este archivo inmediatamente por seguridad</strong>";
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>

<style>
body { font-family: Arial; padding: 20px; background: #f5f5f5; }
h2 { color: #2d5a27; }
</style>