<?php
// admin/login.php — acceso al panel (con límite de intentos por IP).
require_once dirname(__DIR__) . '/inc/auth.php';
af_session_start();
af_security_headers();

if (af_admin_logged()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!af_csrf_check($_POST['csrf'] ?? null)) {
        $error = 'Sesión expirada. Intenta de nuevo.';
    } else {
        $db = af_db();
        if (af_login_throttled($db)) {
            $error = 'Demasiados intentos. Espera ' . ADMIN_ATTEMPT_WINDOW_MIN . ' minutos.';
        } else {
            $user = trim($_POST['username'] ?? '');
            $pass = $_POST['password'] ?? '';
            $stmt = $db->prepare('SELECT id, password_hash FROM admins WHERE username = ?');
            $stmt->execute([$user]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($pass, $admin['password_hash'])) {
                session_regenerate_id(true);          // evita fijación de sesión
                $_SESSION['admin_id'] = (int)$admin['id'];
                header('Location: dashboard.php');
                exit;
            }
            af_register_failed_login($db);
            $error = 'Usuario o contraseña incorrectos.';
        }
    }
}
$csrf = af_csrf_token();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin — FLASHDROP</title>
<link rel="stylesheet" href="../assets/css/y2k.css">
</head>
<body>
<div class="contenedor">
    <img src="../assets/logo.png" alt="FLASHDROP" class="logo-cabecera">
    <p class="subtitulo">★ Panel de control ★</p>

    <div class="msn-window">
        <div class="msn-titlebar">
            <span class="titulo">Iniciar sesión — Admin</span>
            <span class="controles"><span>_</span><span>□</span><span>✕</span></span>
        </div>
        <div class="msn-cuerpo">
            <form method="post" action="login.php" autocomplete="off">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                <div class="campo">
                    <label for="username">Usuario</label>
                    <input type="text" id="username" name="username" required maxlength="40" autocapitalize="none">
                </div>
                <div class="campo">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <?php if ($error): ?><p class="mensaje error"><?= e($error) ?></p><?php endif; ?>
                <button class="btn" type="submit">🔐 Entrar</button>
            </form>
        </div>
    </div>
</div>
<img src="../assets/monito.png" alt="" class="monito-flotante">
</body>
</html>
