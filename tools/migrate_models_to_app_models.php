<?php
/**
 * One-shot: mueve modelos de app/*.php a app/Models/*.php con namespace App\Models.
 * Ejecutar desde la raíz del proyecto: php tools/migrate_models_to_app_models.php
 */

$root = dirname(__DIR__);
$appDir = $root . DIRECTORY_SEPARATOR . 'app';

$modelFiles = [
    'User.php',
    'Review.php',
    'Product.php',
    'Assignment.php',
    'HistoricalServices.php',
    'Service.php',
    'Did.php',
    'Calendar.php',
    'BitacoraHost.php',
    'Bitacora.php',
    'Chat.php',
    'Maintenance.php',
    'Test.php',
    'Asset.php',
    'Campaign.php',
    'Area.php',
    'Failure.php',
    'Position.php',
    'Department.php',
    'Company.php',
    'Incident.php',
    'Componente.php',
    'AuthenticationLog.php',
    'IncidenciaSeguridadDato.php',
    'Sede.php',
    'Ubicacion.php',
    'Employee.php',
    'DetailService.php',
    'DetailTimeFalla.php',
    'DetailUsuariosSoporte.php',
    'DetailIncident.php',
    'DescuentoEstado.php',
    'DetailSede.php',
    'CtgSubcategoria.php',
    'CtgContenido.php',
    'Ctg.php',
    'AssetUser.php',
];

$modelsDir = $appDir . DIRECTORY_SEPARATOR . 'Models';
if (!is_dir($modelsDir)) {
    mkdir($modelsDir, 0755, true);
}

foreach ($modelFiles as $file) {
    $src = $appDir . DIRECTORY_SEPARATOR . $file;
    if (!is_file($src)) {
        fwrite(STDERR, "Skip (not found): $file\n");
        continue;
    }
    $content = file_get_contents($src);
    if (strpos($content, 'namespace App;') !== 0 && strpos($content, "<?php\n\nnamespace App;") === false && strpos($content, "<?php\r\n\r\nnamespace App;") === false) {
        // allow BOM or slight variation
        if (!preg_match('/^<\?php\s*\n\s*namespace App;/', $content)) {
            fwrite(STDERR, "Skip (unexpected namespace): $file\n");
            continue;
        }
    }
    $content = preg_replace('/^namespace App;/m', 'namespace App\Models;', $content, 1);
    $dst = $modelsDir . DIRECTORY_SEPARATOR . $file;
    if (file_put_contents($dst, $content) === false) {
        fwrite(STDERR, "ERROR writing $dst\n");
        exit(1);
    }
    echo "Wrote $dst\n";
    unlink($src);
    echo "Removed $src\n";
}

echo "Done.\n";
