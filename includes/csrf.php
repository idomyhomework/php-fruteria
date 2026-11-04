<?php
// Tokens CSRF para formularios que modifican datos.

require_once __DIR__ . '/auth.php'; // garantiza que la sesión está iniciada

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Campo oculto para poner dentro de cada <form method="post">.
function csrf_campo(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

// Corta la petición si el token no es válido.
function csrf_validar(): void
{
    $enviado = $_POST['csrf_token'] ?? '';
    if (!is_string($enviado) || !hash_equals(csrf_token(), $enviado)) {
        http_response_code(400);
        exit('Token CSRF no válido. Recarga la página e inténtalo de nuevo.');
    }
}
