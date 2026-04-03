# Plan de upgrade Laravel 8 → Laravel 9

Proyecto: HelpDesk  
Referencia oficial: [Upgrade Guide Laravel 9.x](https://laravel.com/docs/9.x/upgrade)

**Estado:** ejecutado en código (Mar-2026). Verificar en cada entorno: `php artisan about`, smoke test y `composer audit`.

---

## 1) Requisitos previos (bloqueantes)

| Requisito | Detalle |
|-----------|---------|
| **PHP** | Laravel 9 exige **PHP ≥ 8.0.2**. Recomendado: **8.1 o 8.2** en local/CI antes del upgrade. |
| **Rama** | Trabajar en rama dedicada (`feature/laravel-9`) con backup de BD y `.env`. |
| **Baseline** | Smoke test en L8: login, roles/permisos, tickets, inventario v2, PDFs, exports Excel. |
| **Tests** | `php artisan test` (aunque sea mínimo) para detectar regresiones tras `composer update`. |

---

## 2) Cambios de plataforma (siempre)

### 2.1 `composer.json`

- **`php`:** `"^8.0.2"` o `"^8.1"` (alinear con servidor de producción).
- **`laravel/framework`:** `^9.0` (resolución típica: 9.52.x).
- **Quitar** `fruitcake/laravel-cors` — en L9 el CORS viene integrado; el middleware es del framework.
- **`facade/ignition`:** sustituir por **`spatie/laravel-ignition`** (requerido en L9 para pantallas de error en desarrollo).
- **`nunomaduro/collision`:** subir a **`^6.0`** (compatible con L9).
- **`phpunit/phpunit`:** mantener **^9.5.8** o valorar **^9.6** según skeleton L9; PHPUnit 10 es opcional y puede implicar más cambios en `tests/`.
- **`fakerphp/faker`:** L9 suele pedir **^1.21** en proyectos nuevos; ajustar si Composer lo exige.

### 2.2 HTTP Kernel — CORS

En `app/Http/Kernel.php`, reemplazar:

```php
\Fruitcake\Cors\HandleCors::class,
```

por:

```php
\Illuminate\Http\Middleware\HandleCors::class,
```

`config/cors.php` se mantiene; solo cambia el middleware (ya no depende del paquete `fruitcake/laravel-cors`).

### 2.3 `RouteServiceProvider` (opcional pero recomendado)

L9 depreca el uso implícito del `$namespace` en rutas. Plan:

1. Tras el upgrade, ir eliminando `protected $namespace` y el `->namespace($this->namespace)` en `mapWebRoutes` / `mapApiRoutes`.
2. Usar **FQCN** en `routes/web.php` y `routes/api.php` (`use App\Http\Controllers\...`) o `Route::controller()` donde aplique.

Se puede hacer **en una segunda PR** para no mezclar con el bump de framework.

### 2.4 Otros puntos de la guía oficial (revisar uno a uno)

- Strings y `lang/`: L9 usa `lang/` en la raíz; si aún tenéis recursos solo en `resources/lang`, seguir la guía de migración de traducciones.
- `app/Exceptions/Handler`: firma de `register()` / reportes (según versión exacta del skeleton L9).
- **Flysystem 3** (si tocáis discos S3/FTP): revisar opciones en `config/filesystems.php`.
- **Symfony Mail**: cambios en configuración de mail si usáis APIs avanzadas.

---

## 3) Paquetes del proyecto — matriz de compatibilidad

Ajustar versiones según lo que resuelva Composer; si algún paquete no soporta L9, buscar sustituto o versión mayor antes de mergear.

| Paquete | Notas L9 |
|---------|----------|
| `laravel/ui` | ^3.4 suele cubrir L9; confirmar con `composer why`. |
| `livewire/livewire` | 2.12.x compatible; valorar fijar `^2.12`. |
| `spatie/laravel-permission` | ^5.x compatible; tras actualizar, verificar namespaces de middleware (`RoleMiddleware`, etc.) en `Kernel.php` si el paquete los cambia. |
| `spatie/laravel-activitylog` | Revisar rama 3.x / 4.x según documentación del paquete para L9. |
| `barryvdh/laravel-dompdf` | Comprobar versión que declare soporte L9. |
| `maatwebsite/excel` | ^3.1 — verificar compatibilidad; puede requerir bump menor. |
| `yajra/laravel-datatables-oracle` | ^9 o ^10 según tabla de versiones del autor. |
| `laravelcollective/html` | Paquete con mantenimiento limitado; si falla la resolución, alternativa: componentes Blade o `spatie/laravel-html`. |
| `simplesoftwareio/simple-qrcode` | Verificar `composer.json` del paquete. |
| `intervention/image` | 2.x suele funcionar; Intervention 3 es otro salto (plan aparte). |

---

## 4) Fases de ejecución recomendadas

### Fase A — Preparación (sin tocar versión de Laravel)

1. Fijar PHP 8.1+ en Laragon/CI.
2. Documentar flujos críticos y ejecutar smoke test en **L8**.
3. `composer audit` y deuda de seguridad conocida.

### Fase B — Upgrade Composer

1. Copia de seguridad de `composer.json` / `composer.lock`.
2. Editar `composer.json` según secciones 2 y 3.
3. `composer update laravel/framework --with-all-dependencies` (o `composer update` en rama aislada).
4. Resolver conflictos de versiones; repetir hasta `composer install` limpio.

### Fase C — Ajustes de código

1. Kernel: CORS (2.2).
2. `config/` y `bootstrap/`: alinear con diff **L8 vs L9** del [Laravel Shift](https://laravelshift.com/) o comparar con [laravel/laravel `9.x` branch](https://github.com/laravel/laravel/tree/9.x).
3. `Handler`, providers, `TrustProxies` si la guía lo indica.
4. Ejecutar `php artisan route:cache` / `config:cache` en entorno de prueba.

### Fase D — Verificación

1. `php artisan about`
2. `php artisan migrate --force` (staging)
3. `php artisan db:seed` solo en entorno desechable (o seeders idempotentes).
4. Pruebas manuales: auth, permisos, Livewire, DataTables, PDF, Excel, inventario.
5. `php artisan test`

### Fase E — Limpieza post-upgrade (siguiente iteración)

1. Quitar `$namespace` del `RouteServiceProvider` y imports en rutas.
2. Sustituir APIs deprecadas que marque `phpstan` / IDE.
3. Opcional: migrar factories del classmap a clases `Database\Factories` y quitar `classmap` de factories en `composer.json`.

---

## 5) Comandos útiles

```bash
php artisan about
php artisan config:clear && php artisan route:clear && php artisan view:clear
composer show laravel/framework
php artisan test
```

Tras cambios en permisos Spatie:

```bash
php artisan permission:cache-reset
```

---

## 6) Criterios de “listo para merge”

- [ ] PHP ≥ 8.0.2 en todos los entornos que despliegan esta rama.
- [ ] `composer install` reproducible; lock commiteado.
- [ ] Sin errores en arranque, login y módulos críticos acordados.
- [ ] Documentación actualizada (`AUDITORIA_*` o changelog interno) con fecha del upgrade L9.

---

## 7) Después de Laravel 9

Según el roadmap global del proyecto: **Laravel 10** (PHP 8.1+), luego **L11**, etc. No mezclar salto de major de Laravel con migración Livewire 2→3 si se puede evitar: un cambio por fase reduce riesgo.
