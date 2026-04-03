<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

/**
 * Fase 0 — Inventario repetible de directivas Blade (@can, @role, …) y
 * apariciones de middleware permission:/role: en rutas y controladores Admin.
 *
 * No interpreta lógica de negocio ni valora la BD; solo el código fuente.
 */
class AuthorizationInventoryCommand extends Command
{
    protected $signature = 'helpdesk:auth-inventory
                            {--json= : Ruta opcional para escribir JSON (p. ej. storage/app/auth-inventory.json)}';

    protected $description = 'Genera inventario de autorización (Blade, rutas web, controladores Admin).';

    public function handle(): int
    {
        $base = base_path();
        $viewsDir = $base.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'views';

        $bladeDirectives = $this->collectBladeDirectives($viewsDir);
        $routeMiddleware = $this->collectRouteMiddleware($base.DIRECTORY_SEPARATOR.'routes'.DIRECTORY_SEPARATOR.'web.php');
        $controllerMiddleware = $this->collectAdminControllerMiddleware(
            $base.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'Controllers'.DIRECTORY_SEPARATOR.'Admin'
        );

        $payload = [
            'generated_at' => now()->toIso8601String(),
            'blade' => $bladeDirectives,
            'routes_web_middleware' => $routeMiddleware,
            'admin_controllers_construct_middleware' => $controllerMiddleware,
        ];

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            $this->error('No se pudo serializar JSON.');

            return self::FAILURE;
        }

        $path = $this->option('json');
        if ($path !== null) {
            File::ensureDirectoryExists(dirname($path));
            File::put($path, $json);
            $this->info("JSON escrito en: {$path}");
        }

        $this->line('');
        $this->info('Resumen Blade (directivas → archivos):');
        foreach ($bladeDirectives as $directive => $files) {
            $this->line(sprintf('  %-28s %d archivo(s)', $directive, count($files)));
        }

        $this->line('');
        $this->info('Rutas web.php — permission: (recuento por permiso):');
        foreach ($routeMiddleware['permissions'] as $perm => $count) {
            $this->line("  [{$count}] {$perm}");
        }

        $this->line('');
        $this->info('Rutas web.php — role: (recuento):');
        foreach ($routeMiddleware['roles'] as $role => $count) {
            $this->line("  [{$count}] {$role}");
        }

        $this->line('');
        $this->info('Controladores Admin — middleware en __construct (recuento por cadena):');
        foreach ($controllerMiddleware as $key => $count) {
            $this->line("  [{$count}] {$key}");
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string, list<string>>
     */
    private function collectBladeDirectives(string $viewsDir): array
    {
        $out = [];
        if (! is_dir($viewsDir)) {
            return $out;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($viewsDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            /** @var \SplFileInfo $file */
            if (strtolower($file->getExtension()) !== 'php') {
                continue;
            }
            $path = $file->getPathname();
            $content = @file_get_contents($path);
            if ($content === false) {
                continue;
            }

            $relative = str_replace(base_path().DIRECTORY_SEPARATOR, '', $path);
            $relative = str_replace('\\', '/', $relative);

            $patterns = [
                'can' => '/@can\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
                'cannot' => '/@cannot\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
                'role' => '/@role\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
                'hasanyrole' => '/@hasanyrole\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
                'hasallroles' => '/@hasallroles\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
            ];

            foreach ($patterns as $directive => $pattern) {
                if (preg_match_all($pattern, $content, $m)) {
                    foreach ($m[1] as $value) {
                        $key = $directive.': '.$value;
                        if (! isset($out[$key])) {
                            $out[$key] = [];
                        }
                        if (! in_array($relative, $out[$key], true)) {
                            $out[$key][] = $relative;
                        }
                    }
                }
            }
        }

        ksort($out);

        return $out;
    }

    /**
     * @return array{permissions: array<string, int>, roles: array<string, int>}
     */
    private function collectRouteMiddleware(string $webPhp): array
    {
        $permissions = [];
        $roles = [];
        if (! is_file($webPhp)) {
            return ['permissions' => $permissions, 'roles' => $roles];
        }

        $content = file_get_contents($webPhp);
        if ($content === false) {
            return ['permissions' => $permissions, 'roles' => $roles];
        }

        if (preg_match_all("/middleware\(\s*['\"]permission:([^'\"]+)['\"]\s*\)/", $content, $m)) {
            foreach ($m[1] as $raw) {
                foreach (preg_split('/\s*\|\s*/', $raw) as $p) {
                    $p = trim($p);
                    if ($p === '') {
                        continue;
                    }
                    $permissions[$p] = ($permissions[$p] ?? 0) + 1;
                }
            }
        }

        if (preg_match_all("/middleware\(\s*['\"]role:([^'\"]+)['\"]\s*\)/", $content, $m2)) {
            foreach ($m2[1] as $raw) {
                foreach (preg_split('/\s*\|\s*/', $raw) as $r) {
                    $r = trim($r);
                    if ($r === '') {
                        continue;
                    }
                    $roles[$r] = ($roles[$r] ?? 0) + 1;
                }
            }
        }

        ksort($permissions);
        ksort($roles);

        return ['permissions' => $permissions, 'roles' => $roles];
    }

    /**
     * Extrae cadenas tipo permission:… o role:… dentro de $this->middleware([...]).
     *
     * @return array<string, int>
     */
    private function collectAdminControllerMiddleware(string $adminDir): array
    {
        $counts = [];
        if (! is_dir($adminDir)) {
            return $counts;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($adminDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            /** @var \SplFileInfo $file */
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $content = @file_get_contents($file->getPathname());
            if ($content === false || ! str_contains($content, 'function __construct')) {
                continue;
            }

            if (preg_match_all("/['\"]permission:([^'\"]+)['\"]/", $content, $m)) {
                foreach ($m[1] as $p) {
                    $counts['permission:'.$p] = ($counts['permission:'.$p] ?? 0) + 1;
                }
            }
            if (preg_match_all("/['\"]role:([^'\"]+)['\"]/", $content, $m2)) {
                foreach ($m2[1] as $r) {
                    $counts['role:'.$r] = ($counts['role:'.$r] ?? 0) + 1;
                }
            }
        }

        ksort($counts);

        return $counts;
    }
}
