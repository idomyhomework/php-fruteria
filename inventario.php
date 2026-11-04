<?php
// Formulario de nuevo producto (solo admin).

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

requerir_admin();

// Valores del último envío fallido, para no tener que reescribirlos.
$old = $_SESSION['old_inventario'] ?? [];
unset($_SESSION['old_inventario']);

$titulo = 'Inventario';
require __DIR__ . '/includes/cabecera.php';
?>
<h1>Añadir producto</h1>

<form class="formulario" method="post" action="accion_fruta.php">
    <?= csrf_campo() ?>
    <input type="hidden" name="accion" value="insertar">
    <label>Nombre
        <input type="text" name="nombre" value="<?= e($old['nombre'] ?? '') ?>" required maxlength="100">
    </label>
    <label>Precio (€)
        <input type="number" name="precio" value="<?= e($old['precio'] ?? '') ?>" required min="0" max="999999.99" step="0.01">
    </label>
    <label>Cantidad
        <input type="number" name="cantidad" value="<?= e($old['cantidad'] ?? '0') ?>" required min="0" step="1">
    </label>
    <label>URL de la imagen
        <input type="url" name="imagen" value="<?= e($old['imagen'] ?? '') ?>" maxlength="500" placeholder="https://…">
    </label>
    <button type="submit">Guardar</button>
</form>
<?php require __DIR__ . '/includes/pie.php'; ?>
