<?php
echo json_encode([
    'test' => 'PHP funcionando correctamente',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion()
], JSON_UNESCAPED_UNICODE);
?>