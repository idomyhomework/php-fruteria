<?php
// Página de inicio.

require_once __DIR__ . '/includes/auth.php';

$titulo = 'Inicio';
require __DIR__ . '/includes/cabecera.php';
?>
<section class="portada">
    <h1>Frutería Online</h1>
    <p>Fruta fresca de temporada.</p>

    <ul class="enlaces-inicio">
        <li><a href="catalogo.php">Ver el catálogo</a></li>
        <?php if (es_admin()): ?>
            <li><a href="inventario.php">Añadir productos al inventario</a></li>
        <?php endif; ?>
        <?php if (!esta_logueado()): ?>
            <li><a href="login.php">Iniciar sesión</a></li>
            <li><a href="registro.php">Registrarse</a></li>
        <?php endif; ?>
    </ul>
</section>
<?php require __DIR__ . '/includes/pie.php'; ?>
