<?php
// Procesa las acciones de admin sobre frutas: insertar, cambiar cantidad, eliminar.
// Solo POST, solo admin, siempre con token CSRF y sentencias preparadas.

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Método no permitido.');
}

requerir_admin();
csrf_validar();

function volver(string $pagina): void
{
    header('Location: ' . $pagina);
    exit;
}

// Entero >= 0, o null si no es válido.
function entero_no_negativo($valor): ?int
{
    $n = filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 2147483647]]);
    return $n === false ? null : $n;
}

$accion = (string) ($_POST['accion'] ?? '');

switch ($accion) {
    case 'insertar':
        $nombre   = trim((string) ($_POST['nombre'] ?? ''));
        $precioTx = str_replace(',', '.', trim((string) ($_POST['precio'] ?? '')));
        $cantidad = entero_no_negativo($_POST['cantidad'] ?? '');
        $imagen   = trim((string) ($_POST['imagen'] ?? ''));

        $errores = [];
        if ($nombre === '' || mb_strlen($nombre) > 100) {
            $errores[] = 'el nombre es obligatorio (máx. 100 caracteres)';
        }
        if (!preg_match('/^\d{1,6}(\.\d{1,2})?$/', $precioTx)) {
            $errores[] = 'el precio debe ser un número >= 0 con hasta 2 decimales';
        }
        if ($cantidad === null) {
            $errores[] = 'la cantidad debe ser un entero >= 0';
        }
        if ($imagen !== '' && (
            strlen($imagen) > 500
            || !filter_var($imagen, FILTER_VALIDATE_URL)
            || !in_array(strtolower((string) parse_url($imagen, PHP_URL_SCHEME)), ['http', 'https'], true)
        )) {
            $errores[] = 'la imagen debe ser una URL http(s) válida';
        }

        if ($errores) {
            $_SESSION['old_inventario'] = [
                'nombre'   => $nombre,
                'precio'   => $_POST['precio'] ?? '',
                'cantidad' => $_POST['cantidad'] ?? '',
                'imagen'   => $imagen,
            ];
            flash('error', 'No se ha guardado: ' . implode('; ', $errores) . '.');
            volver('inventario.php');
        }

        $stmt = $pdo->prepare('INSERT INTO frutas (nombre, precio, cantidad, imagen) VALUES (?, ?, ?, ?)');
        $stmt->execute([$nombre, $precioTx, $cantidad, $imagen]);
        flash('ok', "Producto «{$nombre}» añadido.");
        volver('catalogo.php');

    case 'cantidad':
        $id       = entero_no_negativo($_POST['id'] ?? '');
        $cantidad = entero_no_negativo($_POST['cantidad'] ?? '');
        if ($id === null || $cantidad === null) {
            flash('error', 'La cantidad debe ser un entero >= 0.');
            volver('catalogo.php');
        }

        $stmt = $pdo->prepare('UPDATE frutas SET cantidad = ? WHERE id = ?');
        $stmt->execute([$cantidad, $id]);
        $existe = $pdo->prepare('SELECT nombre FROM frutas WHERE id = ?');
        $existe->execute([$id]);
        $nombre = $existe->fetchColumn();
        if ($nombre === false) {
            flash('error', 'Ese producto no existe.');
        } else {
            flash('ok', "Cantidad de «{$nombre}» actualizada a {$cantidad}.");
        }
        volver('catalogo.php');

    case 'eliminar':
        $objetivo = trim((string) ($_POST['objetivo'] ?? ''));
        if ($objetivo === '') {
            flash('error', 'Indica el nombre o el id del producto a eliminar.');
            volver('catalogo.php');
        }

        // Si es un número se trata como id; si no, como nombre exacto.
        if (ctype_digit($objetivo)) {
            $stmt = $pdo->prepare('SELECT id, nombre FROM frutas WHERE id = ?');
        } else {
            $stmt = $pdo->prepare('SELECT id, nombre FROM frutas WHERE nombre = ?');
        }
        $stmt->execute([$objetivo]);
        $encontradas = $stmt->fetchAll();

        if (!$encontradas) {
            flash('error', "No existe ningún producto «{$objetivo}».");
        } elseif (count($encontradas) > 1) {
            flash('error', "Hay varios productos llamados «{$objetivo}». Elimínalo por su id.");
        } else {
            $del = $pdo->prepare('DELETE FROM frutas WHERE id = ?');
            $del->execute([$encontradas[0]['id']]);
            flash('ok', "Producto «{$encontradas[0]['nombre']}» eliminado.");
        }
        volver('catalogo.php');

    default:
        http_response_code(400);
        exit('Acción no válida.');
}
