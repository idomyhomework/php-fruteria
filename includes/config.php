<?php
// Conexión PDO. Las credenciales están en config.local.php (no versionado).

$configFile = __DIR__ . '/config.local.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('Falta includes/config.local.php (copia config.local.example.php).');
}
$cfg = require $configFile;

$dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
    $cfg['db_host'], $cfg['db_port'], $cfg['db_name']
);

try {
    $pdo = new PDO($dsn, $cfg['db_user'], $cfg['db_pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // El detalle va al log; al visitante no se le muestran datos internos.
    error_log('Error de conexión a la BD: ' . $e->getMessage());
    http_response_code(500);
    exit('Error de conexión con la base de datos.');
}

unset($cfg);
