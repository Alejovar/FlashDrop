<?php
// api/invitados.php — GET lista de invitados habilitados (para tablet pública y admin)
// Sin auth porque la tablet de bebidas la usa cualquiera en la mesa.

require_once dirname(__DIR__) . '/inc/helpers.php';
af_security_headers();

$db = af_db();

// ?todos=1 → incluye deshabilitados (solo para admin)
$incluirDeshabilitados = isset($_GET['todos']) && $_GET['todos'] === '1';

if ($incluirDeshabilitados) {
    af_require_admin_api();
    $rows = $db->query(
        'SELECT id, nombre, habilitado, bebidas FROM invitados ORDER BY nombre ASC'
    )->fetchAll();
} else {
    $rows = $db->query(
        'SELECT id, nombre, bebidas FROM invitados WHERE habilitado = 1 ORDER BY nombre ASC'
    )->fetchAll();
}

af_json(['ok' => true, 'invitados' => $rows]);
