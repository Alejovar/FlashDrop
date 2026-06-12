<?php
require_once dirname(__DIR__) . '/inc/auth.php';
af_session_start();
af_security_headers();
af_require_admin_page();

$db   = af_db();
$csrf = af_csrf_token();

// Estadísticas iniciales
$stats = $db->query(
    'SELECT COUNT(*) total,
            SUM(visible = 1) visibles,
            SUM(visible = 0) ocultas,
            SUM(created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)) lastHour,
            MAX(created_at) lastPhoto
     FROM photos'
)->fetch();

// Historial de logros
$milestones = $db->query(
    'SELECT m.quantity, m.achieved_at, p.filename, p.id as photo_id
     FROM milestones m
     JOIN photos p ON p.id = m.photo_id
     ORDER BY m.quantity DESC'
)->fetchAll();

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
<title>Dashboard &mdash; FLASHDROP</title>
<link rel="stylesheet" href="../assets/css/y2k.css">
<style>
/* --- Dashboard extras --- */
.stats-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:10px; margin-bottom:18px; }
.stat-card { background:var(--negro-panel); border:1px solid rgba(140,160,230,.35); border-radius:10px; padding:14px; text-align:center; }
.stat-num  { font-size:28px; font-weight:900; font-style:italic; background:linear-gradient(180deg,#fff 0%,#d7dcef 38%,#828aa8 50%,#c3c9de 62%,#fff 100%); -webkit-background-clip:text; background-clip:text; color:transparent; }
.stat-lbl  { font-size:10px; color:var(--texto-suave); letter-spacing:1.5px; text-transform:uppercase; margin-top:4px; }
.stat-sub  { font-size:11px; color:var(--azul-cielo); margin-top:2px; }

.admin-card .acciones { flex-direction:row; flex-wrap:wrap; gap:4px; }
.admin-card .acciones a, .admin-card .acciones button { flex:1; min-width:auto; }

.milestone-list { list-style:none; }
.milestone-list li { display:flex; align-items:center; gap:12px; padding:8px 0; border-bottom:1px solid rgba(140,160,230,.12); font-size:13px; color:var(--texto-suave); }
.milestone-list li:last-child { border-bottom:none; }
.milestone-list .m-qty { font-size:16px; font-weight:900; color:var(--azul-cielo); min-width:60px; }
.milestone-list img { width:40px; height:40px; object-fit:cover; border-radius:5px; border:1px solid rgba(140,160,230,.3); }

.online-dot { display:inline-block; width:7px; height:7px; background:var(--ok); border-radius:50%; margin-right:4px; box-shadow:0 0 6px var(--ok); }
</style>
</head>
<body>
<div class="contenedor ancha">
    <img src="../assets/logo.png" alt="FLASHDROP" class="logo-cabecera">
    <p class="subtitulo">DASHBOARD DE ADMINISTRACION</p>

    <!-- === ESTADISTICAS === -->
    <div class="stats-grid" id="stats-grid">
        <div class="stat-card">
            <div class="stat-num" id="stat-total"><?= (int)$stats['total'] ?></div>
            <div class="stat-lbl">Fotos totales</div>
        </div>
        <div class="stat-card">
            <div class="stat-num"><span class="online-dot"></span><span id="stat-online">1</span></div>
            <div class="stat-lbl">Online ahora</div>
        </div>
        <div class="stat-card">
            <div class="stat-num" id="stat-lasthour"><?= (int)$stats['lastHour'] ?></div>
            <div class="stat-lbl">Ultima hora</div>
        </div>
        <div class="stat-card">
            <div class="stat-lbl">Ultima foto</div>
            <div class="stat-sub" id="stat-last"><?= $stats['lastPhoto'] ? e($stats['lastPhoto']) : 'ninguna' ?></div>
        </div>
    </div>

    <!-- === LOGROS === -->
    <?php if ($milestones): ?>
    <div class="msn-window" style="margin-bottom:18px;">
        <div class="msn-titlebar">
            <span class="titulo">Historial de Logros</span>
            <span class="controles"><span>_</span><span>□</span><span>X</span></span>
        </div>
        <div class="msn-cuerpo">
            <ul class="milestone-list">
            <?php foreach ($milestones as $m): ?>
            <li>
                <span class="m-qty"><?= (int)$m['quantity'] ?> MEMORIES</span>
                <img src="../uploads/originals/<?= e($m['filename']) ?>" alt="Foto logro">
                <span>Foto #<?= (int)$m['photo_id'] ?> &mdash; <?= e($m['achieved_at']) ?></span>
            </li>
            <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <!-- === GESTION DE FOTOS === -->
    <div class="msn-window">
        <div class="msn-titlebar">
            <span class="titulo">Gestion de fotos (<?= count($fotos) ?>)</span>
            <span class="controles"><span>_</span><span>□</span><span>X</span></span>
        </div>
        <div class="msn-cuerpo">
            <p class="mensaje" id="msg-admin"></p>
            <?php if (!$fotos): ?>
                <p class="aviso">No hay fotos todavia.</p>
            <?php endif; ?>
            <div class="admin-grid">
                <?php foreach ($fotos as $f): ?>
                <div class="admin-card <?= $f['visible'] ? '' : 'oculta' ?>" data-id="<?= (int)$f['id'] ?>">
                    <img src="../uploads/originals/<?= e($f['filename']) ?>" alt="Foto #<?= (int)$f['id'] ?>" loading="lazy">
                    <div class="estado <?= $f['visible'] ? 'visible' : 'no-visible' ?>">
                        <?= $f['visible'] ? 'VISIBLE' : 'OCULTA' ?>
                    </div>
                    <div class="acciones">
                        <button class="btn mini <?= $f['visible'] ? 'peligro' : '' ?> btn-toggle" type="button">
                            <?= $f['visible'] ? 'OCULTAR' : 'RESTAURAR' ?>
                        </button>
                        <button class="btn mini secundario btn-replay" type="button" <?= $f['visible'] ? '' : 'disabled' ?>>
                            PANTALLA
                        </button>
                        <a class="btn mini secundario btn-dl-original" href="../api/download.php?id=<?= (int)$f['id'] ?>" download>
                            ORIGINAL
                        </a>
                        <a class="btn mini secundario btn-dl-polaroid" href="../api/polaroid.php?id=<?= (int)$f['id'] ?>&admin=1" download>
                            POLAROID
                        </a>
                        <button class="btn mini peligro btn-delete" type="button">
                            ELIMINAR
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- === ACCIONES GLOBALES === -->
    <div class="menu-opciones" style="max-width:500px; margin:0 auto 30px;">
        <a class="btn" href="../api/album.php" download="AlejoFest_Vol21.zip">DESCARGAR ALBUM COMPLETO</a>
        <a class="btn secundario" href="../pantalla.php" target="_blank" rel="noopener">ABRIR PANTALLA GRANDE</a>
        <a class="btn secundario" href="../galeria.php" target="_blank" rel="noopener">VER GALERIA PUBLICA</a>
        <a class="btn peligro" href="logout.php">CERRAR SESION</a>
    </div>
</div>
<script src="../assets/js/admin.js"></script>
</body>
</html>
