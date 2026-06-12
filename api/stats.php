<?php
// api/stats.php — estadísticas en tiempo real: total fotos, online, última hora.
// El conteo "online" usa sesiones activas con timestamp en DB (approach sencillo vía cookie ping).

require_once dirname(__DIR__) . '/inc/helpers.php';
af_security_headers();

$db = af_db();

// Total fotos
$total = (int)$db->query('SELECT COUNT(*) FROM photos WHERE visible = 1')->fetchColumn();

// Fotos en la última hora
$lastHour = (int)$db->query('SELECT COUNT(*) FROM photos WHERE created_at > (NOW() - INTERVAL 1 HOUR)')->fetchColumn();

// Última foto subida (timestamp)
$lastRow  = $db->query('SELECT created_at FROM photos WHERE visible = 1 ORDER BY id DESC LIMIT 1')->fetch();
$lastPhoto = $lastRow ? $lastRow['created_at'] : null;

// "Online": conteo de IPs únicas activas en últimos 3 minutos (via photos + screen_queue pings)
// Estrategia sencilla: IPs que subieron foto en últimos 5 min + pings registrados
$online = (int)$db->query(
    'SELECT COUNT(DISTINCT uploader_ip) FROM photos WHERE created_at > (NOW() - INTERVAL 5 MINUTE)'
)->fetchColumn();
// Valor mínimo de 1 (el admin siempre está) para que no quede en 0
if ($online < 1) $online = 1;

af_json([
    'ok'        => true,
    'total'     => $total,
    'online'    => $online,
    'lastHour'  => $lastHour,
    'lastPhoto' => $lastPhoto,
]);
