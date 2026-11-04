<?php
require_once __DIR__ . '/../includes/admin.php';

if (adminActual()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();
    $usuario = trim((string)($_POST['usuario'] ?? ''));
    $clave = (string)($_POST['clave'] ?? '');
    try {
        $consulta = conexion()->prepare('SELECT id, usuario, clave_hash FROM administradores WHERE usuario = ?');
        $consulta->execute([$usuario]);
        $admin = $consulta->fetch();
        if ($admin && password_verify($clave, $admin['clave_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int)$admin['id'];
            $_SESSION['admin_usuario'] = $admin['usuario'];
            header('Location: index.php');
            exit;
        }
        sleep(1); // frena intentos repetidos
        $error = 'Usuario o contraseña incorrectos.';
    } catch (PDOException $e) {
        error_log('FreshCuttz admin login: ' . $e->getMessage());
        $error = 'No hay conexión con la base de datos. Revisa que MySQL esté encendido en XAMPP.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Iniciar sesión · Fresh Cuttz Admin</title>
    <link rel="stylesheet" href="admin.css?v=<?= filemtime(__DIR__ . '/admin.css') ?>">
</head>

<body class="pantalla-login">
    <main class="login-admin">
        <div class="login-marca">
            <img src="../image/logo1.webp" width="44" height="33" alt="">
            <span>Fresh Cuttz</span>
        </div>
        <div class="bloque login-tarjeta">
            <h1>Iniciar sesión</h1>
            <p>Accede al panel de administración.</p>
            <form method="post">
                <?= campoCsrf() ?>
                <label class="campo">Usuario
                    <input name="usuario" type="text" autocomplete="username" required autofocus value="<?= e($_POST['usuario'] ?? '') ?>">
                </label>
                <label class="campo">Contraseña
                    <input name="clave" type="password" autocomplete="current-password" required>
                </label>
                <?php if ($error): ?>
                    <div class="aviso aviso-error" role="alert"><?= icono('error') ?><p><?= e($error) ?></p></div>
                <?php endif; ?>
                <button class="boton" type="submit">Entrar</button>
            </form>
        </div>
        <p class="login-pie"><a href="../index.php">Volver al sitio</a></p>
    </main>
</body>

</html>
