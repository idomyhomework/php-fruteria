<?php
// Formulario de registro. Siempre crea usuarios con rol 'usuario'.

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

if (esta_logueado()) {
    header('Location: index.php');
    exit;
}

$errores = [];
$nombre  = '';
$email   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();

    $nombre    = trim((string) ($_POST['nombre_usuario'] ?? ''));
    $email     = trim((string) ($_POST['email'] ?? ''));
    $password  = (string) ($_POST['password'] ?? '');
    $password2 = (string) ($_POST['password2'] ?? '');

    if (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $nombre)) {
        $errores[] = 'El nombre de usuario debe tener entre 3 y 50 caracteres (letras, números o _).';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 120) {
        $errores[] = 'El email no es válido.';
    }
    if (strlen($password) < 8) {
        $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
    }
    if ($password !== $password2) {
        $errores[] = 'Las contraseñas no coinciden.';
    }

    if (!$errores) {
        $stmt = $pdo->prepare('SELECT nombre_usuario, email FROM usuarios WHERE nombre_usuario = ? OR email = ?');
        $stmt->execute([$nombre, $email]);
        foreach ($stmt->fetchAll() as $fila) {
            if (strcasecmp($fila['nombre_usuario'], $nombre) === 0) {
                $errores[] = 'Ese nombre de usuario ya está en uso.';
            }
            if (strcasecmp($fila['email'], $email) === 0) {
                $errores[] = 'Ese email ya está registrado.';
            }
        }
    }

    if (!$errores) {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO usuarios (nombre_usuario, email, password_hash, rol) VALUES (?, ?, ?, 'usuario')"
            );
            $stmt->execute([$nombre, $email, password_hash($password, PASSWORD_DEFAULT)]);
            header('Location: login.php?registrado=1');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') { // clave duplicada (registro simultáneo)
                $errores[] = 'Ese nombre de usuario o email ya está registrado.';
            } else {
                throw $e;
            }
        }
    }
}

$titulo = 'Registro';
require __DIR__ . '/includes/cabecera.php';
?>
<h1>Crear cuenta</h1>

<?php if ($errores): ?>
    <ul class="errores">
        <?php foreach ($errores as $err): ?>
            <li><?= e($err) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form class="formulario" method="post" action="registro.php">
    <?= csrf_campo() ?>
    <label>Nombre de usuario
        <input type="text" name="nombre_usuario" value="<?= e($nombre) ?>" required maxlength="50" pattern="[A-Za-z0-9_]{3,50}">
    </label>
    <label>Email
        <input type="email" name="email" value="<?= e($email) ?>" required maxlength="120">
    </label>
    <label>Contraseña
        <input type="password" name="password" required minlength="8">
    </label>
    <label>Repite la contraseña
        <input type="password" name="password2" required minlength="8">
    </label>
    <button type="submit">Registrarme</button>
</form>

<p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
<?php require __DIR__ . '/includes/pie.php'; ?>
