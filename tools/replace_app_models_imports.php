<?php
/**
 * Reemplaza referencias App\X por App\Models\X (modelos migrados).
 * php tools/replace_app_models_imports.php
 */

$root = dirname(__DIR__);

$models = [
    'HistoricalServices', 'IncidenciaSeguridadDato', 'CtgSubcategoria', 'CtgContenido',
    'DetailUsuariosSoporte', 'DetailIncident', 'DescuentoEstado', 'DetailSede', 'DetailService', 'DetailTimeFalla',
    'AuthenticationLog', 'Componente', 'Assignment', 'BitacoraHost', 'Maintenance', 'Department', 'Position',
    'Campaign', 'Company', 'Failure', 'Product', 'Incident', 'Calendar', 'Bitacora', 'Review', 'Service',
    'AssetUser', 'Employee', 'Ubicacion', 'Asset', 'Area', 'Chat', 'Ctg', 'Did', 'Sede', 'Test', 'User',
];

usort($models, function ($a, $b) {
    return strlen($b) - strlen($a);
});

$dirs = [
    $root . '/app',
    $root . '/routes',
    $root . '/config',
    $root . '/database',
    $root . '/tests',
    $root . '/resources',
];

$exclude = ['/vendor/', '/node_modules/', '/storage/framework/', '/bootstrap/cache/'];

function walkPhp($dir, $callback) {
    if (!is_dir($dir)) {
        return;
    }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($it as $f) {
        /** @var SplFileInfo $f */
        if (!$f->isFile()) {
            continue;
        }
        $p = $f->getPathname();
        if (!preg_match('/\.(php|blade\.php)$/', $p)) {
            continue;
        }
        $callback($p);
    }
}

$files = [];
foreach ($dirs as $dir) {
    walkPhp($dir, function ($p) use (&$files, $exclude, $root) {
        foreach ($exclude as $ex) {
            if (strpos(str_replace('\\', '/', $p), trim($ex, '/')) !== false) {
                return;
            }
        }
        if (strpos($p, $root . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'migrate_models') !== false) {
            return;
        }
        $files[] = $p;
    });
}

$changed = 0;

foreach ($files as $file) {
    $content = file_get_contents($file);
    $orig = $content;

    foreach ($models as $m) {
        $content = str_replace('use App\\' . $m . ';', 'use App\\Models\\' . $m . ';', $content);
        $content = str_replace('\\App\\' . $m . '::', '\\App\\Models\\' . $m . '::', $content);
        $content = str_replace('\\App\\' . $m . ' ', '\\App\\Models\\' . $m . ' ', $content);
    }

    // Limpieza por si hubo doble reemplazo
    $content = str_replace('App\\Models\\Models\\', 'App\\Models\\', $content);

    if ($content !== $orig) {
        file_put_contents($file, $content);
        $changed++;
        echo "Updated: $file\n";
    }
}

echo "Files changed: $changed\n";
