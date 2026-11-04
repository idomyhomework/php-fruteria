<?php
// Cierra la sesión. Solo acepta POST con token CSRF (botón de la cabecera).

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Método no permitido.');
}

csrf_validar();
cerrar_sesion();

header('Location: index.php');
exit;
