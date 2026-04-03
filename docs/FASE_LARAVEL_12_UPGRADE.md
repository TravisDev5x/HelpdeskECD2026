# Upgrade a Laravel 12 (compatibilidad)

## Dependencias actualizadas

| Paquete | Antes | Después |
|---------|-------|---------|
| `laravel/framework` | ^11.0 | **^12.0** (12.55.x) |
| `yajra/laravel-datatables-oracle` | ^11.0 | **^12.0** (requiere `illuminate/*` ^12) |
| `barryvdh/laravel-dompdf` | ^2.0 | **^3.0** (Dompdf 3.x; soporte L12) |

`composer update -W` resolvió el árbol sin conflictos.

## Compatibilidad aplicada en config

- **`config/hashing.php`**: alineado con Laravel 12 (`HASH_DRIVER`, `BCRYPT_LIMIT`, `ARGON_*` por env) manteniendo **rounds por defecto 10** y valores Argon conservadores del proyecto.

## Ya cubierto antes de L12 (sin cambios extra obligatorios)

- **Disco `local`**: `config/filesystems.php` define explícitamente `root => storage_path('app')` → no aplica el nuevo default `storage/app/private` de L12.
- **Carbon 3**: ya en uso desde L11.
- **UUID / `HasUuids`**: no se usa en modelos del proyecto.
- **Facade PDF**: `barryvdh/laravel-dompdf` v3 sigue registrando alias `PDF` / `Pdf`.

## Guía oficial — revisar si aplica en el futuro

- [Laravel 12.x Upgrade](https://laravel.com/docs/12.x/upgrade): regla `image` sin SVG por defecto, `mergeIfMissing` con notación punto, precedencia de rutas con mismo nombre, `Concurrency::run` con claves asociativas, etc.

## Verificación

```bash
php artisan about
php artisan test
```

Prueba manual recomendada: vistas con **DataTables**, generación de **PDF** (resguardo inventario), **Livewire** inventario/admin.
