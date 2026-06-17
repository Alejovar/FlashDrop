<?php
// api/bebidas_top.php — top 5 invitados con más bebidas. Para pantalla.php.

require_once dirname(__DIR__) . '/inc/helpers.php';
af_security_headers();

$db = af_db();

$rows = $db->query(
    'SELECT nombre, bebidas FROM invitados
     WHERE habilitado = 1 AND bebidas > 0
     ORDER BY bebidas DESC, nombre ASC
     LIMIT 5'
)->fetchAll();

af_json(['ok' => true, 'top' => $rows]);
