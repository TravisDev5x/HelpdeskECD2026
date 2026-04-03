# Auditoría en profundidad — compatibilidad Laravel (histórico L9 → **actual L10**)

**Fecha:** Mar-2026  
**Versión actual verificada:** Laravel **10.50.x** (`php artisan about`), PHP **≥ 8.1** (recomendado 8.2+).  
*Documento originado en auditoría L9; riesgos de CVE y dependencias quedaron mitigados subiendo a L10.48+.*

---

## 1) Resumen ejecutivo

| Área | Estado |
|------|--------|
| Arranque / `artisan about` | OK |
| Middleware HTTP (CORS, maintenance, etc.) | OK |
| Dependencias Composer (sin `fruitcake/laravel-cors`, con `spatie/laravel-ignition`) | OK |
| Spatie Activity Log v4 (`getActivitylogOptions`) | OK en modelos auditados |
| `php artisan route:list` | OK |
| `php artisan route:cache` | **Corregido** (nombres de rutas duplicados) |
| `php artisan config:cache` | OK |
| Tests PHPUnit del repo | OK (2 tests) |
| Seguridad `composer audit` | **Riesgo:** CVE en ramas &lt; L10.48.29 (ver §6) |

---

## 2) Bootstrap y núcleo

- **`bootstrap/app.php`:** Estructura clásica L8/L9 (singletons de `Http\Kernel`, `Console\Kernel`, `ExceptionHandler`). Correcto.
- **`public/index.php`:** Estándar. Correcto.
- **`app/Http/Kernel.php`:** Usa **`Illuminate\Http\Middleware\HandleCors`** (adecuado para L9 sin paquete `fruitcake/laravel-cors`).
- **`app/Http/Middleware/PreventRequestsDuringMaintenance`:** Extiende la clase correcta del framework.
- **`app/Http/Middleware/CheckForMaintenanceMode`:** Sigue existiendo como **alias** en el framework (`CheckForMaintenanceMode extends PreventRequestsDuringMaintenance`). No está registrado en `Kernel`; archivo **redundante** pero no rompe L9. Opcional: eliminar el archivo si no se usa.

---

## 3) Proveedores y rutas

- **`RouteServiceProvider`:** Sigue usando **`$namespace`** y `->namespace($this->namespace)` en `mapWebRoutes` / `mapApiRoutes`. En L9 está **deprecado** pero **sigue funcionando**. Recomendación futura: FQCN en `routes/*.php` y quitar `$namespace` (mejora alineación con L10+).
- **Rutas duplicadas (crítico para producción):** Laravel 9 serializa rutas en `route:cache`. Varios nombres estaban **duplicados** (mismo `->name()` en rutas distintas), lo que provocaba `LogicException` al ejecutar `php artisan route:cache`.

### Correcciones aplicadas en esta auditoría

1. **`password.update`:** Colisionaba entre **reset de contraseña** (`Auth::routes`) y **POST `/password/change`**. El POST de cambio pasó a llamarse **`password.change.submit`**.
2. **`admin.assignments.store`:** Duplicado entre `Route::resource('assignments')` y `POST admin/assignments/store`. Eliminada la ruta redundante (el formulario ya usa `route('admin.assignments.store')` → resource).
3. **`admin.sedes.store` / `admin.sedes.update` / `admin.sedes.destroy`:** Duplicados frente al `resource`. Eliminadas rutas legacy; el resource cubre store/update/destroy.
4. **`admin.ubicaciones.store` / `admin.ubicaciones.update` / `admin.ubicaciones.destroy`:** Igual que sedes.
5. **`admin.components.*`:** Duplicados frente al `resource`. Eliminadas rutas legacy; la vista de listado de componentes actualizada con **`@method('DELETE')`** para el borrado.

Tras los cambios: **`php artisan route:cache` finaliza con éxito.**

---

## 4) Configuración

- **`config/filesystems.php`:** Usa **`FILESYSTEM_DRIVER`** en `env()`. En skeleton L9 suele usarse **`FILESYSTEM_DISK`**; el nombre antiguo **sigue siendo válido** si está definido en `.env`. Recomendación: unificar a `FILESYSTEM_DISK` en `.env` y en config cuando convenga.
- **Discos `local`:** Compatibles con **Flysystem 3** (L9). Si en el futuro usáis S3 con opciones estrictas, revisar la guía L9/L10 para `throw` y `report`.
- **`config/mail.php`:** Estructura con `mailers` y Symfony Mailer; coherente con L9.
- **Traducciones:** Archivos en **`resources/lang`**. Válido en L9; la guía moderna sugiere carpeta **`lang/`** en la raíz — **opcional**, no bloqueante.

---

## 5) Modelos y código de aplicación

- **`$dates` en modelos** (`AuthenticationLog`, `Incident`, `Product`): En Eloquent moderno se prefiere **`$casts`** con `'atributo' => 'datetime'`. No rompe L9; es **deuda menor**.
- **Helpers globales tipo `str_`:** No se detectó uso problemático; uso de funciones nativas/`Str` mezclado es aceptable.
- **Paquete `laravelcollective/html`:** Composer lo marca como **abandonado**; sustituto sugerido: `spatie/laravel-html`. Planificar migración a medio plazo.

---

## 6) Seguridad y dependencias

- **`composer audit`:** Reporta **CVE-2025-27515** (validación de archivos) en versiones de **`laravel/framework` &lt; 10.48.29** (y otras ramas mayores). Permanece en **Laravel 9** implica aceptar riesgo residual o aplicar mitigaciones (validación estricta en uploads, WAF, etc.). **Mitigación estructural:** planificar **upgrade a Laravel 10.48.29+** (o superior según roadmap).
- **`minimum-stability: dev` + `prefer-stable`:** Puede resolver `laravel/framework` como **`9.x-dev`** en el lock. Funcional, pero para producción conviene **stability estable** y versiones fijadas cuando el audit lo permita.

---

## 7) Comandos recomendados tras despliegue

```bash
php artisan about
php artisan config:cache
php artisan route:cache
php artisan view:cache   # opcional
php artisan permission:cache-reset   # si tocáis permisos Spatie
composer audit
php artisan test
```

En desarrollo, tras depurar rutas:

```bash
php artisan route:clear
php artisan config:clear
```

---

## 8) Conclusión

El proyecto está **alineado con Laravel 10** (kernel, CORS, paquetes, Activity Log v4). Los **nombres de rutas duplicados** quedaron corregidos (permite `route:cache`). **Laravel Collective** se retiró (formularios en Blade nativo). **`$dates`** en modelos clave migró a **`$casts`**. **`composer audit`** sin advisories en el lock actual. Mejoras opcionales: quitar `$namespace` del `RouteServiceProvider` (FQCN en rutas) y seguir roadmap hacia L11+.
