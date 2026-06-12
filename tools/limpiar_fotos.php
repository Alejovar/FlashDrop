<?php
// tools/limpiar_fotos.php — elimina TODOS los registros de fotos, la cola y los logros.
// Los archivos físicos en uploads/originals/ también se borran.
// Ejecutar solo desde CLI o con acceso directo al servidor.
// USO: php tools/limpiar_fotos.php [--confirmar]

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Solo CLI.');
}

$confirmar = in_array('--confirmar', $argv ?? [], true);

if (!$confirmar) {
    echo "ATENCION: esto elimina TODAS las fotos, la cola y los logros.\n";
    echo "Para confirmar ejecuta: php tools/limpiar_fotos.php --confirmar\n";
    exit(0);
}

require_once dirname(__DIR__) . '/inc/helpers.php';
$db = af_db();

// Obtener archivos antes de borrar
$fotos = $db->query('SELECT filename FROM photos')->fetchAll(PDO::FETCH_COLUMN);

// Limpiar tablas en orden (FK)
$db->exec('DELETE FROM milestones');
$db->exec('DELETE FROM screen_queue');
$db->exec('DELETE FROM photos');
$db->exec('ALTER TABLE photos AUTO_INCREMENT = 1');
$db->exec('ALTER TABLE screen_queue AUTO_INCREMENT = 1');
$db->exec('ALTER TABLE milestones AUTO_INCREMENT = 1');

// Borrar archivos físicos
$uploadsDir = dirname(__DIR__) . '/uploads/originals/';
$borrados = 0;
foreach ($fotos as $filename) {
    $path = $uploadsDir . $filename;
    if (file_exists($path)) {
        unlink($path);
        $borrados++;
    }
}

echo "Listo.\n";
echo "Registros eliminados: " . count($fotos) . "\n";
echo "Archivos borrados: $borrados\n";
