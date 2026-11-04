<?php
// Cabecera HTML y navegación común.
// Uso: $titulo = 'Catálogo'; require __DIR__ . '/includes/cabecera.php';

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';

$titulo = $titulo ?? 'Frutería Online';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo) ?> · Frutería Online</title>
    <link rel="stylesheet" href="assets/css/estilo.css">
    <script src="assets/js/app.js" defer></script>
</head>
<body>
<header class="cabecera">
    <a class="logo" href="index.php">Frutería Online</a>
    <nav>
        <a href="index.php">Inicio</a>
        <a href="catalogo.php">Catálogo</a>
        <?php if (es_admin()): ?>
            <a href="inventario.php">Inventario</a>
        <?php endif; ?>

        <?php if (esta_logueado()): ?>
            <span class="usuario">Hola, <?= e($_SESSION['nombre_usuario']) ?></span>
            <form class="form-logout" method="post" action="logout.php">
                <?= csrf_campo() ?>
                <button type="submit">Cerrar sesión</button>
            </form>
        <?php else: ?>
            <a href="login.php">Iniciar sesión</a>
            <a href="registro.php">Registrarse</a>
        <?php endif; ?>
    </nav>
</header>
<main>
<?php if ($flash = flash_obtener()): ?>
    <p class="flash flash-<?= e($flash['tipo']) ?>"><?= e($flash['mensaje']) ?></p>
<?php endif; ?>
