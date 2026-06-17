<?php
// api/invitados_contador.php — total de invitados confirmados (público, para invitacion.php)

require_once dirname(__DIR__) . '/inc/helpers.php';
af_security_headers();

$db = af_db();

$total = (int)$db->query('SELECT COUNT(*) FROM invitados WHERE habilitado = 1')->fetchColumn();

af_json(['ok' => true, 'total' => $total]);