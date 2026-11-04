<?php
// Catálogo público: tabla de frutas y búsqueda por nombre.
// El admin además puede cambiar cantidades y eliminar productos.

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

// Siempre se cargan todos los productos; la búsqueda filtra la tabla en el navegador (app.js).
$frutas = $pdo->query('SELECT id, nombre, precio, cantidad, imagen FROM frutas ORDER BY nombre')->fetchAll();
$admin = es_admin();

$titulo = 'Catálogo';
require __DIR__ . '/includes/cabecera.php';
?>
<h1>Catálogo</h1>

<!-- Filtra la tabla al escribir (app.js); al vaciar el campo se ven todos. -->
<form class="buscador" role="search" onsubmit="return false;">
    <input type="search" id="buscar-fruta" placeholder="Buscar por nombre…" aria-label="Buscar por nombre" autocomplete="off">
</form>

<p id="sin-resultados" hidden>No hay productos que coincidan con la búsqueda.</p>

<?php if (!$frutas): ?>
    <p>No hay productos.</p>
<?php else: ?>
    <div class="tabla-contenedor">
        <table class="tabla-frutas">
            <thead>
                <tr>
                    <?php if ($admin): ?>
                        <th>Id</th><?php endif; ?>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <?php if ($admin): ?>
                        <th>Acciones</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($frutas as $f): ?>
                    <tr data-nombre="<?= e($f['nombre']) ?>">
                        <?php if ($admin): ?>
                            <td><?= (int) $f['id'] ?></td><?php endif; ?>
                        <td>
                            <?php if ($f['imagen']): ?>
                                <img src="<?= e($f['imagen']) ?>" alt="<?= e($f['nombre']) ?>" loading="lazy" width="64"
                                    height="64">
                            <?php else: ?>
                                <span class="sin-imagen">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($f['nombre']) ?></td>
                        <td><?= number_format((float) $f['precio'], 2, ',', '.') ?> €</td>
                        <td>
                            <?php if ($admin): ?>
                                <form class="form-cantidad" method="post" action="accion_fruta.php">
                                    <?= csrf_campo() ?>
                                    <input type="hidden" name="accion" value="cantidad">
                                    <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                                    <input type="number" name="cantidad" value="<?= (int) $f['cantidad'] ?>" min="0" step="1"
                                        required aria-label="Cantidad de <?= e($f['nombre']) ?>">
                                    <button type="submit">Guardar</button>
                                </form>
                            <?php else: ?>
                                <?= (int) $f['cantidad'] ?>
                            <?php endif; ?>
                        </td>
                        <?php if ($admin): ?>
                            <td>
                                <form class="form-eliminar-fila" method="post" action="accion_fruta.php"
                                    data-confirm="¿Eliminar «<?= e($f['nombre']) ?>»?">
                                    <?= csrf_campo() ?>
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="objetivo" value="<?= (int) $f['id'] ?>">
                                    <button type="submit" class="peligro">Eliminar</button>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/pie.php'; ?>