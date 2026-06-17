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

// Invitados confirmados (RSVP)
$invitados = $db->query(
    'SELECT id, nombre, habilitado, bebidas FROM invitados ORDER BY nombre ASC'
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

.invitados-lista { max-height:340px; overflow-y:auto; display:flex; flex-direction:column; gap:8px; }
.invitado-row {
    display:flex; align-items:center; gap:10px; padding:10px 12px;
    background:rgba(140,160,230,.06); border:1px solid rgba(140,160,230,.18);
    border-radius:8px; flex-wrap:wrap;
}
.invitado-row.deshabilitado { opacity:.5; background:rgba(255,80,80,.05); border-color:rgba(255,80,80,.2); }
.invitado-nombre { font-weight:600; font-size:13px; flex:1; min-width:120px; }
.invitado-bebidas { font-size:12px; color:var(--azul-cielo); white-space:nowrap; }
.invitado-estado { font-size:10px; letter-spacing:1px; color:var(--texto-suave); text-transform:uppercase; white-space:nowrap; }
.invitado-row.deshabilitado .invitado-estado { color:#ff8888; }
.btn-toggle-invitado, .btn-eliminar-invitado { white-space:nowrap; }

.loops-lista { display:flex; flex-direction:column; gap:8px; max-height:280px; overflow-y:auto; }
.loop-row {
    display:flex; align-items:center; gap:10px; padding:10px 12px;
    background:rgba(140,160,230,.06); border:1px solid rgba(140,160,230,.18);
    border-radius:8px;
}
.loop-row.activo { border-color:var(--azul-cielo); background:rgba(31,86,255,.1); box-shadow:0 0 12px rgba(31,86,255,.2); }
.loop-nombre { flex:1; font-size:13px; font-weight:600; word-break:break-all; }
.loop-estado { font-size:10px; letter-spacing:1px; color:var(--azul-cielo); text-transform:uppercase; white-space:nowrap; }
.btn-usar-loop { white-space:nowrap; }
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

    <!-- === LOOP DE VIDEO === -->
    <div class="msn-window" style="max-width:500px; margin:0 auto 18px;">
        <div class="msn-titlebar">
            <span class="titulo">Video de fondo &mdash; pantalla.php</span>
            <span class="controles"><span>_</span><span>□</span><span>X</span></span>
        </div>
        <div class="msn-cuerpo">
            <p class="aviso" style="margin-bottom:14px;">
                Selecciona el video que se reproduce en la pantalla grande. Cambia en vivo sin recargar.
            </p>
            <p class="mensaje" id="msg-loop"></p>
            <div id="loops-lista" class="loops-lista">
                <p style="text-align:center; color:var(--texto-suave); padding:14px 0;">Cargando videos...</p>
            </div>
        </div>
    </div>

    <!-- === INVITADOS / RSVP === -->
    <div class="msn-window" style="max-width:700px; margin:0 auto 18px;">
        <div class="msn-titlebar">
            <span class="titulo">Invitados confirmados (RSVP)</span>
            <span class="controles"><span>_</span><span>□</span><span>X</span></span>
        </div>
        <div class="msn-cuerpo">
            <p class="aviso" style="margin-bottom:14px;">
                Deshabilita a quien no asista. Los deshabilitados no aparecen en la tablet de bebidas.
            </p>
            <p class="mensaje" id="msg-invitados"></p>

            <div id="invitados-lista" class="invitados-lista">
                <?php if (empty($invitados)): ?>
                    <p style="text-align:center; color:var(--texto-suave); padding:20px 0;">
                        Aún no hay invitados confirmados.
                    </p>
                <?php else: foreach ($invitados as $inv): ?>
                    <div class="invitado-row <?= $inv['habilitado'] ? '' : 'deshabilitado' ?>" data-id="<?= (int)$inv['id'] ?>">
                        <span class="invitado-nombre"><?= e($inv['nombre']) ?></span>
                        <span class="invitado-bebidas"><?= (int)$inv['bebidas'] ?> bolsitas</span>
                        <span class="invitado-estado"><?= $inv['habilitado'] ? 'CONFIRMADO' : 'DESHABILITADO' ?></span>
                        <button class="btn mini btn-toggle-invitado" type="button">
                            <?= $inv['habilitado'] ? 'DESHABILITAR' : 'HABILITAR' ?>
                        </button>
                        <button class="btn mini peligro btn-eliminar-invitado" type="button">
                            ELIMINAR
                        </button>
                    </div>
                <?php endforeach; endif; ?>
            </div>

            <div class="menu-opciones" style="margin-top:14px;">
                <a class="btn secundario" href="../bebidas.php" target="_blank" rel="noopener">ABRIR TABLET DE BEBIDAS</a>
            </div>
        </div>
    </div>

    <!-- === TEST DE ANIMACIONES 1=== -->
    <div class="msn-window" style="max-width:500px; margin:0 auto 18px;">
        <div class="msn-titlebar">
            <span class="titulo">Test de animaciones &mdash; pantalla.php</span>
            <span class="controles"><span>_</span><span>□</span><span>X</span></span>
        </div>
        <div class="msn-cuerpo">
            <p class="aviso" style="margin-bottom:14px;">
                Dispara animaciones en la pantalla grande en tiempo real.<br>
                Requiere que <strong>pantalla.php</strong> esté abierta y activa.
            </p>
            <p class="mensaje" id="msg-test"></p>

            <div class="menu-opciones" style="gap:10px;">
                <!-- Toast MSN -->
                <button class="btn secundario" id="btn-test-toast" type="button">
                    PROBAR VENTANA MSN (TOAST)
                </button>

                <!-- Milestone con selector de cantidad -->
                <div style="display:flex; gap:8px; align-items:center;">
                    <button class="btn secundario" id="btn-test-milestone" type="button" style="flex:1;">
                        PROBAR LOGRO
                    </button>
                    <select id="sel-milestone-qty" style="
                        background:var(--negro-panel);
                        color:var(--texto);
                        border:1px solid rgba(140,160,230,.4);
                        border-radius:8px;
                        padding:10px 10px;
                        font-family:inherit;
                        font-size:13px;
                        cursor:pointer;
                        min-width:90px;
                    ">
                        <option value="15">15</option>
                        <option value="30">30</option>
                        <option value="45">45</option>
                        <option value="60">60</option>
                        <option value="75">75</option>
                        <option value="90">90</option>
                        <option value="105">105</option>
                    </select>
                </div>
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