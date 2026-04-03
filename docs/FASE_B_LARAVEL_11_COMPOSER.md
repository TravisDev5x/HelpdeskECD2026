# Fase B — Laravel 11 (Composer + dependencias)

**Estado:** completada (`composer update` OK, `php artisan about`, `php artisan route:list`, `php artisan test`).

## Versiones instaladas (referencia)

| Paquete | Antes (L10) | Después |
|---------|-------------|---------|
| `laravel/framework` | 10.50.2 | **11.50.0** |
| `spatie/laravel-permission` | 5.11.x | **6.25.0** |
| `yajra/laravel-datatables-oracle` | 10.11.x | **11.1.6** |
| `nunomaduro/collision` | 7.12.x | **8.9.1** |
| `phpunit/phpunit` | 10.5.x | **11.5.55** |
| `nesbot/carbon` | 2.x | **3.x** (vía framework) |
| Symfony | 6.4.x | **7.4.x** |

## Cambios en `composer.json`

- `laravel/framework`: `^11.0`
- `spatie/laravel-permission`: `^6.0`
- `yajra/laravel-datatables-oracle`: `^11.0`
- `nunomaduro/collision`: `^8.1`
- `phpunit/phpunit`: `^11.0`
- `spatie/laravel-ignition`: `^2.8`
- `laravel/tinker`: `^2.9`
- `fakerphp/faker`: `^1.23`

## Código ajustado en esta fase

- **`app/Http/Kernel.php`:** middleware de Spatie v6 usa namespace `Spatie\Permission\Middleware\*` (antes `Middlewares\*`).
- **`phpunit.xml`:** esquema actualizado a PHPUnit **11.5**.

## Bootstrap / esqueleto

La app sigue usando **`bootstrap/app.php` clásico** + **`App\Http\Kernel`** (compatible con Laravel 11). Migrar a `Application::configure(...)` es **opcional** (fase posterior de alineación con el skeleton L11).

## Próximos pasos sugeridos (fuera de Fase B)

1. Pruebas manuales: login, permisos, DataTables, Livewire (inventario, auditoría), exportaciones Excel, PDF.
2. Revisar [Upgrade Guide 11.x](https://laravel.com/docs/11.x/upgrade) por deprecaciones en código propio.
3. Valorar migración de `bootstrap/app.php` y alias `middlewareAliases` en el Kernel.
