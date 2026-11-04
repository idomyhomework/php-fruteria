<?php
// Sesión y control de acceso por rol.

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}

function esta_logueado(): bool
{
    return isset($_SESSION['usuario_id']);
}

function es_admin(): bool
{
    return esta_logueado() && ($_SESSION['rol'] ?? '') === 'admin';
}

// Guarda el usuario en sesión tras un login correcto.
function iniciar_sesion_usuario(array $usuario): void
{
    session_regenerate_id(true); // evita fijación de sesión
    $_SESSION['usuario_id']     = (int) $usuario['id'];
    $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];
    $_SESSION['rol']            = $usuario['rol'];
}

function cerrar_sesion(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

// Sin sesión: redirige al login.
function requerir_login(): void
{
    if (!esta_logueado()) {
        header('Location: login.php');
        exit;
    }
}

// Sin sesión: login. Con sesión pero sin rol admin: 403.
function requerir_admin(): void
{
    requerir_login();
    if (!es_admin()) {
        http_response_code(403);
        exit('403 - Acceso denegado: solo administradores.');
    }
}

// Escapa texto para mostrarlo en HTML (prevención de XSS).
function e(?string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

// Mensaje de un solo uso para mostrar tras una redirección ('ok' o 'error').
function flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

function flash_obtener(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}
