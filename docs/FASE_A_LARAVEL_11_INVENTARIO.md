# Fase A — Inventario y compatibilidad (Laravel 11)

**Fecha:** 2026-02-17  
**Rama Git:** `chore/laravel-11-fase-a` (creada desde `feature/user-audit-module`)  
**Objetivo:** Cumplir requisitos de PHP para L11, inventariar dependencias y **bloqueos verificados con Composer** (sin instalar Laravel 11 aún).

---

## 1. Entorno verificado (local)

| Comprobación | Resultado |
|--------------|-----------|
| PHP CLI | **8.3.26** (≥ 8.2 ✓) |
| Laravel actual | **10.50.2** |
| Livewire | **3.7.11** |
| `composer validate` | **OK** |
| `composer update --dry-run` (con `php: ^8.2`) | **Sin cambios necesarios en lock** |
| `php artisan about` | **OK** |

**Servidor / CI:** Asegurar **PHP ≥ 8.2** en todos los despliegues antes de la fase B.

---

## 2. Cambios aplicados en el repo (Fase A)

| Archivo | Cambio |
|---------|--------|
| `composer.json` | `"php": "^8.1"` → **`"php": "^8.2"`** (requisito Laravel 11) |
| `.env.example` | Comentario documentando **PHP ^8.2** |

> **Compatibilidad:** El lock actual sigue siendo válido; no se forzó actualización de paquetes. Quien aún use **PHP 8.1** dejará de poder instalar dependencias hasta actualizar PHP.

---

## 3. Bloqueos para `laravel/framework:11.0.0` (Composer `why-not`)

Comando: `composer why-not laravel/framework 11.0.0`

### 3.1 Bloqueos directos (acción en Fase B)

| Paquete (instalado) | Motivo | Acción prevista Fase B |
|---------------------|--------|-------------------------|
| **Restricción en `composer.json`** | `laravel/framework: ^10.48` | Cambiar a `^11.0` cuando el resto esté alineado |
| **nunomaduro/collision** `7.12.0` | `conflicts laravel/framework (>=11.0.0)` | Subir a **`collision ^8.0`** (compatible con L11) |
| **spatie/laravel-permission** `5.11.1` | `illuminate/*` solo hasta **^10.0** | Subir a **`spatie/laravel-permission ^6.0`** (revisar [upgrade del paquete](https://github.com/spatie/laravel-permission/blob/main/docs/upgrading.md)) |
| **yajra/laravel-datatables-oracle** `10.11.4` | `illuminate/*` **^9 \| ^10** | Subir a **`^11.0`** del paquete Datatables para Laravel 11 |

### 3.2 Resolución transitiva (Composer lo ajustará con el framework)

- **Symfony 7** (console, http-kernel, etc.) entra con Laravel 11; no hace falta fijarlo a mano salvo conflicto residual.
- **nunomaduro/termwind** `^2.0` lo pedirá Laravel 11.
- **mockery/mockery:** `why-not` reportó conflicto con ciertas versiones respecto a `laravel/framework 11.0.0`. Resolver en el **primer `composer update` real** a L11 (puede requerir pin o versión de `mockery`/`phpunit` acorde a la matriz final).

---

## 4. Inventario por paquete (direct `require`)

| Paquete | Versión lock (referencia) | Compatible con L11 (según metadatos actuales) | Notas Fase B |
|---------|---------------------------|-----------------------------------------------|--------------|
| `laravel/framework` | 10.50.2 | Objetivo **^11** | Guía oficial [Upgrade 10 → 11](https://laravel.com/docs/11.x/upgrade) |
| `livewire/livewire` | 3.7.11 | Sí (mantener ^3.5+; revisar notas de versión al subir framework) | Ya en v3 |
| `spatie/laravel-activitylog` | 4.12.1 | Sí (`illuminate/*` incluye **^11**) | Subir constraint si Composer lo pide |
| `spatie/laravel-permission` | 5.11.1 | **No** con L11 | **Bump mayor a ^6** |
| `yajra/laravel-datatables-oracle` | 10.11.4 | **No** con L11 | **Bump a rama 11.x** del paquete |
| `barryvdh/laravel-dompdf` | 2.2.0 | Sí (`illuminate/support` incluye **^11**); existe **v3** mayor | Opcional: valorar DomPDF 3 en otro PR |
| `maatwebsite/excel` | 3.1.68 | Sí (`illuminate/support` incluye **^11**) | — |
| `laravel/ui` | 4.6.3 | Sí (`illuminate/*` incluye **^11**) | Legacy UI; sin bloqueo técnico conocido |
| `simplesoftwareio/simple-qrcode` | 4.2.0 | No declara `illuminate/*`; uso típico OK | Probar tras `composer update` |
| `intervention/image` | 2.7.2 | Independiente del core; PHP 8.2+ OK | Migración a Intervention v3 = trabajo aparte |
| `laravel/tinker` | 2.11.1 | Actualizar con el skeleton L11 / Composer | Major 3.x disponible |
| `laravel/legacy-factories` | 1.x | Evaluar si aún necesario | Puede retirarse si no hay factories antiguas |
| `guzzlehttp/guzzle` | ^7 | Compatible | — |

### require-dev

| Paquete | Versión | Fase B |
|---------|---------|--------|
| `nunomaduro/collision` | 7.12.0 | **^8.0** |
| `phpunit/phpunit` | 10.5.x | Valorar **PHPUnit 11** alineado con L11 |
| `spatie/laravel-ignition` | 2.9.x | Actualizar a versión compatible con L11 |
| `laravel/sail`, `fakerphp/faker`, `mockery/mockery` | — | Resolver con el árbol final tras subir framework |

---

## 5. `composer outdated --direct` (mayores disponibles)

Solo informativo; **no** implica migrar todo de golpe:

- `laravel/framework` → hasta 13.x en registro Packagist (objetivo intermedio: **11**).
- `livewire/livewire` → 4.x (fuera de alcance L11; mantener v3 salvo decisión explícita).
- `intervention/image` → 3.x (opcional).
- `yajra/laravel-datatables-oracle` → 13.x (subir al menos a la línea que soporte **L11**).
- `spatie/laravel-permission` → 7.x (paso razonable: **6.x** para L11, luego evaluar).

---

## 6. Checklist siguiente fase (B)

- [ ] Fusionar o basar trabajo en `chore/laravel-11-fase-a` cuando cierre la rama de features en curso (o merge selectivo).
- [ ] Actualizar `laravel/framework`, `collision`, `spatie/laravel-permission`, `yajra/laravel-datatables-oracle`.
- [ ] `composer update` y corregir incompatibilidades de código + guía L11.
- [ ] Ajustar esqueleto (`bootstrap/app.php`, middleware, `routes/console.php`).
- [ ] Pruebas: auth, Livewire inventario/auditoría, DataTables, Excel, PDF.

---

## 7. Referencias

- [Laravel 11.x Upgrade Guide](https://laravel.com/docs/11.x/upgrade)
- [Laravel Release Support](https://laravel.com/docs/releases)
