<?php
// Formulario de inicio de sesión (por nombre de usuario o email).

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

if (esta_logueado()) {
    header('Location: index.php');
    exit;
}

$error = '';
$identificador = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();

    $identificador = trim((string) ($_POST['identificador'] ?? ''));
    $password      = (string) ($_POST['password'] ?? '');

    $stmt = $pdo->prepare(
        'SELECT id, nombre_usuario, password_hash, rol FROM usuarios WHERE nombre_usuario = ? OR email = ? LIMIT 1'
    );
    $stmt->execute([$identificador, $identificador]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($password, $usuario['password_hash'])) {
        if (password_needs_rehash($usuario['password_hash'], PASSWORD_DEFAULT)) {
            $upd = $pdo->prepare('UPDATE usuarios SET password_hash = ? WHERE id = ?');
            $upd->execute([password_hash($password, PASSWORD_DEFAULT), $usuario['id']]);
        }
        iniciar_sesion_usuario($usuario);
        header('Location: index.php');
        exit;
    }

    // Mismo mensaje si falla el usuario o la contraseña: no se revela cuál.
    $error = 'Usuario o contraseña incorrectos.';
}

$titulo = 'Iniciar sesión';
require __DIR__ . '/includes/cabecera.php';
?>
<h1>Iniciar sesión</h1>

<?php if (isset($_GET['registrado'])): ?>
    <p class="aviso">Cuenta creada correctamente. Ya puedes iniciar sesión.</p>
<?php endif; ?>

<?php if ($error): ?>
    <p class="errores"><?= e($error) ?></p>
<?php endif; ?>

<form class="formulario" method="post" action="login.php">
    <?= csrf_campo() ?>
    <label>Usuario o email
        <input type="text" name="identificador" value="<?= e($identificador) ?>" required>
    </label>
    <label>Contraseña
        <input type="password" name="password" required>
    </label>
    <button type="submit">Entrar</button>
</form>

<p>¿No tienes cuenta? <a href="registro.php">Regístrate</a></p>
<?php require __DIR__ . '/includes/pie.php'; ?>
