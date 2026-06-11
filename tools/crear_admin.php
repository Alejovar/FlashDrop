<?php
// tools/crear_admin.php — crea o actualiza un admin. SOLO desde la terminal:
//   php tools/crear_admin.php usuario contraseña
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Este script solo se ejecuta desde la línea de comandos.');
}
require_once dirname(__DIR__) . '/inc/helpers.php';

if ($argc < 3) {
    exit("Uso: php tools/crear_admin.php <usuario> <contraseña>\n");
}
[$_, $user, $pass] = $argv;
if (strlen($pass) < 10) {
    exit("La contraseña debe tener al menos 10 caracteres.\n");
}

$hash = password_hash($pass, PASSWORD_DEFAULT);
$db = af_db();
$db->prepare(
    'INSERT INTO admins (username, password_hash) VALUES (?, ?)
     ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)'
)->execute([$user, $hash]);

echo "✅ Admin '{$user}' listo. Entra en /admin/login.php\n";
