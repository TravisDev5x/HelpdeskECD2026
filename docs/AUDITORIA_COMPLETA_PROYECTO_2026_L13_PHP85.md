# Auditoria Completa del Proyecto HelpDesk (2026)

## Objetivo

Documentar el estado tecnico real del proyecto, el stack utilizado, errores y riesgos a corregir, areas de oportunidad y un plan por fases para actualizar a Laravel 13 con PHP 8.5 manteniendo compatibilidad, seguridad y continuidad operativa.

---

## 1) Resumen ejecutivo

- El sistema esta actualmente en una base **Laravel 7.30.x** con una arquitectura mixta de **Blade + AdminLTE + Livewire 2 + DataTables**.
- La migracion a **Laravel 13 / PHP 8.5** es viable, pero **no se recomienda de salto directo**; debe hacerse por versiones mayores consecutivas.
- Hay deuda tecnica relevante en:
  - dependencias legacy/EOL,
  - autorizacion inconsistente por ruta,
  - riesgos de XSS en renderizado HTML dinamico,
  - baja cobertura de pruebas automatizadas.
- Prioridad inmediata: corregir seguridad y estabilizar base de pruebas antes de upgrades grandes.

---

## 2) Stack actual confirmado

## Backend

- **Framework:** Laravel 7.30.4 (`laravel/framework`).
- **PHP declarado en Composer:** `^7.2.5 | ^8.0`.
- **Autenticacion/autorizacion:**
  - `Auth::routes()` + sesiones web.
  - `spatie/laravel-permission` (roles/permisos por middleware).
  - Middleware custom de inactividad de sesion.
- **Auditoria de cambios:** `spatie/laravel-activitylog`.
- **Exportables:** `maatwebsite/excel`.
- **PDF:** `barryvdh/laravel-dompdf`.

## Frontend

- **UI principal:** Blade + AdminLTE 3 (Bootstrap 4, jQuery).
- **Componentes dinamicos:** Livewire 2.
- **Tablas pesadas:** DataTables (server-side en varios modulos).
- **Build:** Laravel Mix 5, Vue 2, axios 0.19, Sass.

## Infraestructura/configuracion

- Uso de middleware y patrones legacy de Laravel 7 (`CheckForMaintenanceMode`, `fideloper/proxy`, `fruitcake/laravel-cors`).
- Composer con `minimum-stability: dev` (riesgo de resolucion menos estricta).

---

## 3) Hallazgos de auditoria (priorizados)

## Criticos

1. **Riesgo XSS en vistas con HTML dinamico no escapado**  
   Se detecta salida de contenido con `{!! !!}` y composicion de HTML desde datos de BD en algunos flujos de auditoria/tablas.

2. **Control de autorizacion desigual en rutas administrativas**  
   Existen endpoints protegidos solo por `auth` sin permiso granular en todos los casos sensibles.

3. **Base framework/dependencias sin soporte vigente**  
   Laravel 7 y varias librerias del stack actual ya no estan en su mejor ventana de soporte de seguridad.

## Altos

4. **Livewire 2 fijado a version antigua (`2.0`)**  
   Riesgo alto de compatibilidad con versiones modernas de Laravel/PHP.

5. **Modelos con asignacion masiva permisiva (`$guarded = []`)**  
   Aumenta superficie de riesgo ante cambios de request o validaciones incompletas.

6. **Dependencias legacy de middleware de plataforma**  
   `fideloper/proxy` y `fruitcake/laravel-cors` deben migrarse al enfoque nativo en versiones modernas.

## Medios

7. **Mezcla de estilos de rutas/controladores**  
   Dificulta mantenimiento y upgrades.

8. **Toolchain frontend desactualizado**  
   Mix 5, Vue 2, Bootstrap 4 y axios 0.19 incrementan deuda tecnica y posibles CVE.

9. **Cobertura de pruebas insuficiente**  
   Alto riesgo de regresiones en migraciones de versiones mayores.

---

## 4) Errores y correcciones recomendadas (corto plazo)

1. **Blindar salida HTML en vistas**
   - Sustituir `{!! !!}` por `{{ }}` cuando aplique.
   - Escapar siempre contenido dinamico en renderizadores JS/DataTables.

2. **Endurecer autorizacion en rutas admin**
   - Revisar matriz de endpoints vs permisos.
   - Aplicar middleware `permission`/`role` donde falte.

3. **Reducir riesgo de mass assignment**
   - Migrar progresivamente a `$fillable` en modelos sensibles.
   - Mantener Form Requests estrictos.

4. **Ajustar Composer a resolucion estable**
   - Cambiar `minimum-stability` a `stable` (manteniendo `prefer-stable`).
   - Eliminar paquetes redundantes (ej. `illuminate/support` directo).

5. **Corregir inconsistencias de rutas**
   - Unificar convencion `Controller::class`.
   - Detectar prefijos duplicados o endpoints legacy no usados.

---

## 5) Areas de oportunidad (mejora funcional y tecnica)

- **Observabilidad:** logging estructurado por modulo y trazas de errores por contexto.
- **Rendimiento:** cache de consultas pesadas en monitoreo/informes y optimizacion de indices SQL.
- **UX:** estandarizar filtros guardados, estados vacios, feedback de carga y acciones responsive en todas las tablas.
- **Seguridad:** politica CSP/headers, sanitizacion centralizada y revisiones regulares de dependencias.
- **Calidad:** pipeline CI con pruebas, lint y auditoria de seguridad automatica.

---

## 6) Plan de actualizacion a Laravel 13 + PHP 8.5

## Principio de ejecucion

No migrar de 7 a 13 de un solo salto. Ejecutar por etapas con ramas y validacion funcional por cada version mayor.

## Fase 0 - Preparacion (1-2 semanas)

- Inventario final de dependencias (Composer y NPM) con compatibilidad objetivo.
- Congelar cambios funcionales grandes durante la migracion.
- Definir ambiente de staging identico a produccion.
- Crear baseline de pruebas minimas:
  - login,
  - permisos por rol,
  - flujo principal de tickets,
  - inventario v2 (listado/edicion/monitoreo/exportables).

## Fase 1 - Endurecimiento previo (1-2 semanas) ✅ COMPLETADA

- ~~Corregir hallazgos criticos de seguridad (XSS/autorizacion).~~ **Hecho** (Parte 1 y 2)
  - Parte 1: XSS corregido en audit-table, dashboard DataTables, users index.
  - Parte 2: Middleware de autorizacion agregado a rutas sensibles (services, users, calendar, reports, etc.).
- ~~Reducir deuda bloqueante:~~ **Hecho** (Parte 3)
  - Parte 3: `$guarded = []` eliminado de **todos** los modelos del proyecto (20 modelos).
    Reemplazado por `$fillable` explicito verificado contra columnas reales de BD.
    Modelos corregidos: Service, Product, Assignment, Incident, Company, Department,
    Position, Failure, Area, Campaign, Asset, Test, Maintenance, Calendar, Chat, Did,
    HistoricalServices, Review, Bitacora, BitacoraHost + modelos Inv*.
  - limpiar rutas legacy (pendiente menor),
  - estabilizar scripts DataTables sensibles (parcial - escapeHtml aplicado).

## Fase 2 - Upgrade de plataforma por pasos (4-8 semanas) - EN PROGRESO

Ruta recomendada:

1. ~~Laravel 7 -> 8~~ ✅ **COMPLETADO** (17-Feb-2026)
   - Framework: laravel/framework 7.30.4 → 8.83.29
   - PHP: 8.3.26 (compatible)
   - Livewire: 2.0 → 2.12.8
   - Spatie Permission: 3.18 → 5.11.1
   - Spatie ActivityLog: 3.14.1 → 3.17.0 (se mantiene v3 API, compatible L8)
   - laravel/ui: 2.5 → 3.4.6
   - Se agrego: laravel/legacy-factories (soporte de factories clasicas)
   - Middleware actualizado: TrustProxies, PreventRequestsDuringMaintenance
   - Paginacion: forzada a Bootstrap (Paginator::useBootstrap)
   - Se elimino: fideloper/proxy (integrado en L8)
   - **Fase A - PSR-4 controladores** ✅ (Mar-2026): namespaces `App\Http\Controllers\Admin` unificados; eliminados duplicados `InventoryPdfController` (raiz) y `AssetUserController` (carpeta Admin vacio).
   - **Fase B - PSR-4 modelos Sede/Ubicacion** ✅ (Mar-2026): `app/sede.php` → `app/Sede.php`, `app/ubicacion.php` → `app/Ubicacion.php` (namespace `App\` sin cambios; imports existentes validos). En Git (Linux) si el nombre no actualiza mayusculas: `git mv -f sede.php Sede_temp.php && git mv Sede_temp.php Sede.php`.
  - **Fase C - Modelos en `App\Models` (convencion Laravel 8)** ✅ (Mar-2026): Todos los Eloquent que estaban en `app/*.php` (User, Service, Product, catalogos, etc.) movidos a `app/Models/` con namespace `App\Models`. Inventario V2 (`Inv*`) ya estaba en `Models`. Scripts de apoyo: `tools/migrate_models_to_app_models.php`, `tools/replace_app_models_imports.php`. `config/auth.php` apunta a `App\Models\User::class`.
   - **Post-Fase C - Spatie morph `model_type`** ✅: Migracion `2026_03_21_000001_update_morph_model_type_app_user_to_app_models_user.php` actualiza `model_has_roles`, `model_has_permissions` y `activity_log` de `App\User` a `App\Models\User`. Sin esto el usuario pierde roles/permisos en UI. Tras desplegar: `php artisan migrate` y `php artisan permission:cache-reset`.
2. ~~Laravel 8 -> 9~~ ✅ **COMPLETADO** (Mar-2026): `laravel/framework` ^9.x, PHP `^8.0.2`, CORS nativo, `spatie/laravel-ignition`, Activity Log v4, DataTables ^10, etc. Detalle: `docs/PLAN_UPGRADE_LARAVEL_9.md`.
3. ~~Laravel 9 -> 10~~ ✅ **COMPLETADO** (Mar-2026): `laravel/framework` **^10.48** (p. ej. 10.50.x), PHP **^8.1**, `laravel/ui` ^4, PHPUnit 10, `spatie/laravel-ignition` ^2, `nunomaduro/collision` ^7, **`minimum-stability: stable`**, sin **`laravelcollective/html`** (formularios roles/permisos/bitácora en HTML estándar). Mitiga CVE de validación de archivos de L9 y **`composer audit` limpio**. Ver `docs/AUDIT_LARAVEL_9_PROFUNDO.md` (actualizado a L10).
4. Laravel 10 -> 11  
5. Laravel 11 -> 12  
6. Laravel 12 -> 13

En paralelo:
- subir PHP gradualmente hasta **8.5** (idealmente 8.2 -> 8.3 -> 8.5),
- aplicar guia oficial de cada major,
- ejecutar pruebas y smoke test por etapa.

## Fase 3 - Livewire y frontend moderno (2-4 semanas)

- Migrar **Livewire 2 -> 3** con revision de cada componente.
- Evaluar paso de **Mix a Vite**.
- Normalizar librerias frontend (bootstrap/tooling) segun compatibilidad final.

## Fase 4 - Estabilizacion y despliegue (1-2 semanas)

- Hardening final de seguridad.
- Pruebas de regresion, carga y UAT.
- Release gradual (canary/porcentaje) + plan de rollback.

---

## 7) Riesgos de migracion y mitigacion

- **Riesgo:** ruptura en componentes Livewire.
  - **Mitigacion:** pruebas por componente + feature flags para modulos criticos.

- **Riesgo:** incompatibilidad de paquetes de terceros.
  - **Mitigacion:** matriz de versiones y reemplazos planificados por paquete.

- **Riesgo:** regresiones silenciosas en permisos/rutas.
  - **Mitigacion:** pruebas de autorizacion por endpoint critico.

- **Riesgo:** tiempos altos de salida a produccion.
  - **Mitigacion:** despliegues iterativos por fase y no big-bang.

---

## 8) Checklist de ejecucion recomendado

- [ ] Cerrar hallazgos criticos de seguridad.
- [ ] Establecer CI minimo (tests + lint + audit dependencias).
- [ ] Definir matriz de compatibilidad de paquetes para L8..L13.
- [ ] Ejecutar upgrades por major en ramas dedicadas.
- [ ] Migrar Livewire 2 a 3.
- [ ] Completar hardening y pruebas de regresion.
- [ ] Desplegar por etapas con rollback probado.

---

## 9) Bitácora Laravel 8 — Seeders (`database/seeders`)

- **Estructura oficial L8:** todos los seeders viven en `database/seeders/` con namespace `Database\Seeders` y extienden `Illuminate\Database\Seeder`.
- **Composer:** se eliminó `database/seeds` del `classmap`; el autoload PSR-4 `Database\\Seeders\\` → `database/seeders/` es la fuente de verdad.
- **Renombrado:** `RolesAndPermissions` → `RolesAndPermissionsSeeder`; `DatabaseSeeder` usa `$this->call([...])` con `use Illuminate\Support\Facades\DB` para `FOREIGN_KEY_CHECKS`.
- **InvMigrationSeeder:** seeder destructivo (truncate + migración desde `Product`); **no** está incluido en el `run()` por defecto. Ejecución manual:
  `php artisan db:seed --class=Database\\Seeders\\InvMigrationSeeder`
- Tras clonar o cambiar seeders: `composer dump-autoload` y `php artisan db:seed` (solo en entornos donde aplique borrar/rellenar datos).

---

## 10) Conclusion

El proyecto es recuperable y modernizable. La mejor estrategia para llegar a **Laravel 13 + PHP 8.5** con bajo riesgo es:

1) seguridad y estabilizacion primero,  
2) upgrades graduales por version mayor,  
3) migracion de Livewire/tooling frontend al final,  
4) pruebas automatizadas como puerta obligatoria en cada fase.

Con este enfoque se reduce riesgo operativo, se mejora mantenibilidad y se asegura continuidad del negocio.

