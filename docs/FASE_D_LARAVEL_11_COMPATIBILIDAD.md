# Fase D — Compatibilidad código/config con Laravel 11

Alineación con la [guía oficial de upgrade 10.x → 11.x](https://laravel.com/docs/11.x/upgrade) en los puntos que afectan a este proyecto.

## 1. Autenticación y hashing (impacto alto/medio)

| Tema | Acción aplicada |
|------|-----------------|
| **Password rehash on login** | Laravel 11 puede actualizar el hash al iniciar sesión si cambia el work factor. El mutador `setPasswordAttribute` del `User` **hasheaba siempre**, lo que **rompía** el guardado de un hash ya calculado. |
| **Solución** | Usar `Hash::isHashed($password)` y solo aplicar `Hash::make()` a texto plano. |
| **Config** | `config/hashing.php`: `rehash_on_login` (env `HASH_REHASH_ON_LOGIN`), `verify` en `bcrypt` y `argon` (env `HASH_VERIFY`), alineado con el framework. |
| **Documentación env** | `.env.example` comenta `HASH_VERIFY` y `HASH_REHASH_ON_LOGIN`. |

**Archivos:** `app/Models/User.php`, `config/hashing.php`, `.env.example`

## 2. Carbon 3 (impacto medio)

| Tema | Acción aplicada |
|------|-----------------|
| **`diffIn*` devuelve `float`** | Puede mostrar decimales en vistas. |
| **Signo** | Por defecto `diffInMinutes` usa `$absolute = false`; para duración de sesión se usa **`true`** para evitar valores negativos. |
| **Vista** | `active-sessions.blade.php`: `diffInMinutes(..., true)` y cast a entero con `round` para mostrar minutos enteros. |

**Archivo:** `resources/views/livewire/admin/sessions/active-sessions.blade.php`

## 3. Middleware y convenciones L11 (impacto bajo)

| Tema | Acción aplicada |
|------|-----------------|
| **`$routeMiddleware` deprecado** | Sustituido por **`$middlewareAliases`** en `App\Http\Kernel` (mismo mapa de alias). El framework sigue fusionando ambos si existieran; aquí solo queda el nombre recomendado. |
| **`Authenticate::redirectTo`** | Firma alineada con el padre: `Request $request` tipado y retorno `?string` (incluye `return null` explícito para JSON). |

**Archivos:** `app/Http/Kernel.php`, `app/Http/Middleware/Authenticate.php`

## 4. Modelo `User` — casts

| Campo | Cast |
|-------|------|
| `password_expires_at` | `datetime` |
| `fecha_baja` | `date` |

Mejora coherencia con Eloquent y fechas en Carbon 3.

## 5. Verificaciones realizadas (este repo)

| Ítem guía L11 | Estado |
|---------------|--------|
| Dependencias / PHP 8.2 | Cubierto en Fase B |
| Estructura tipo skeleton L11 (`bootstrap/app.php` mínimo) | **No obligatorio**; la guía indica que apps L10 pueden mantener estructura anterior |
| Migraciones `->change()` sin repetir modificadores | **Sin usos** de `->change()` en `database/migrations` |
| `unsignedDecimal` / `unsignedDouble` / `unsignedFloat` eliminados | **No encontrados** en migraciones |
| Relación Eloquent llamada `casts()` (choque con método base) | **No existe** en `app/Models` |
| Paquetes Sanctum/Passport/Telescope/Cashier | **No instalados** (N/A) |
| `doctrine/dbal` en `composer.json` | **No listado** como dependencia directa |
| Contratos custom (`UserProvider`, `Authenticatable`, etc.) | **No hay implementaciones propias** |

## 6. Pendientes opcionales (fuera de Fase D)

- Auditar **nuevas migraciones** futuras: al usar `->change()`, repetir todos los modificadores de columna según la guía.
- Valorar **squash** de migraciones + `schema:dump` si el historial crece mucho.
- Pruebas manuales: **login**, cambio de contraseña, sesiones activas (historial con duración en minutos).

## 7. Comprobación local

```bash
php artisan about
php artisan test
```
