<?php
// admin/dashboard.php — panel: ocultar/restaurar fotos y reproducirlas en pantalla.
require_once dirname(__DIR__) . '/inc/auth.php';
af_session_start();
af_security_headers();
af_require_admin_page();

$db = af_db();
$csrf = af_csrf_token();

$stats = $db->query(
    'SELECT COUNT(*) total,
            SUM(visible = 1) visibles,
            SUM(visible = 0) ocultas
     FROM photos'
)->fetch();

$fotos = $db->query(
    'SELECT id, filename, orientation, visible, created_at
     FROM photos ORDER BY id DESC LIMIT 500'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf" content="<?= e($csrf) ?>">
<title>Dashboard — AlejoFest Vol.21</title>
<link rel="stylesheet" href="../assets/css/y2k.css">
</head>
<body>
<div class="contenedor ancha">
    <img src="../assets/logo.png" alt="AlejoFest Vol.21" class="logo-cabecera">
    <p class="subtitulo">★ Dashboard de admin ★</p>

    <div class="admin-stats">
        <div class="stat"><div class="num chrome-text"><?= (int)$stats['total'] ?></div><div class="etq">Fotos totales</div></div>
        <div class="stat"><div class="num chrome-text"><?= (int)$stats['visibles'] ?></div><div class="etq">Visibles</div></div>
        <div class="stat"><div class="num chrome-text"><?= (int)$stats['ocultas'] ?></div><div class="etq">Ocultas</div></div>
    </div>

    <div class="msn-window">
        <div class="msn-titlebar">
            <span class="titulo">Gestión de fotos</span>
            <span class="controles"><span>_</span><span>□</span><span>✕</span></span>
        </div>
        <div class="msn-cuerpo">
            <p class="mensaje" id="msg-admin"></p>
            <?php if (!$fotos): ?>
                <p class="aviso">Aún no hay fotos subidas.</p>
            <?php endif; ?>
            <div class="admin-grid">
                <?php foreach ($fotos as $f): ?>
                <div class="admin-card <?= $f['visible'] ? '' : 'oculta' ?>" data-id="<?= (int)$f['id'] ?>">
                    <img src="../uploads/<?= e($f['filename']) ?>" alt="Foto #<?= (int)$f['id'] ?>" loading="lazy">
                    <div class="estado <?= $f['visible'] ? 'visible' : 'no-visible' ?>">
                        <?= $f['visible'] ? '● Visible' : '● Oculta' ?>
                    </div>
                    <div class="acciones">
                        <button class="btn mini <?= $f['visible'] ? 'peligro' : '' ?> btn-toggle" type="button">
                            <?= $f['visible'] ? '🚫 Ocultar' : '♻ Restaurar' ?>
                        </button>
                        <button class="btn mini secundario btn-replay" type="button" <?= $f['visible'] ? '' : 'disabled' ?>>
                            📺 Reproducir en pantalla
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="menu-opciones" style="max-width:420px; margin:0 auto;">
        <a class="btn secundario" href="../pantalla.php" target="_blank" rel="noopener">🖥️ Abrir pantalla grande</a>
        <a class="btn secundario" href="../galeria.php" target="_blank" rel="noopener">🖼️ Ver galería pública</a>
        <a class="btn peligro" href="logout.php">🔒 Cerrar sesión</a>
    </div>
</div>
<script src="../assets/js/admin.js"></script>
</body>
</html>
