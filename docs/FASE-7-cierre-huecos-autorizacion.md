# Fase 7 — Cierre de huecos priorizados (autorización)

Estado del inventario original y acciones tomadas en el repo.

## Alta prioridad

| Hueco | Estado |
|-------|--------|
| **ver roles vs read roles** (menú vs `RoleController` / `RolesManager`) | **Cerrado** en fases anteriores: menú y comentarios usan `read roles`; no quedan `ver roles` en el código. |
| `productos/download_all` sin `permission:` en ruta | **Cerrado**: ruta `producto.downloadall` con `middleware('permission:descarga_productosall')` y migración/seeder del permiso. |
| **BitacoraHost vs Bitacora** (middleware distinto) | **Cerrado**: `BitacoraHostController` ya tenía middleware por acción; `BitacoraController` alineado al mismo patrón (`read` + `create` para mutaciones CRUD). |

## Media prioridad

| Hueco | Estado |
|-------|--------|
| **ServicesController** — `update` comentado | **Cerrado**: `permission:update service` en `update` y `relanzarServicio`; `read services` en lecturas/validación. |
| **excel.productos.\*** vs **read reports inventory** | **Cerrado en Fase 7**: botones de export en `inventory-dashboard` pasan a `@can('read reports inventory')`, coherente con la página y con las rutas `report.download.*` (OR con `read reports ticket`). |

## Baja prioridad

| Hueco | Estado |
|-------|--------|
| **FormRequest** con `authorize() === true` | **Cerrado** en Fase 4: `CreateUserRequest`, `UpdateUserRequest`, `Update*`, `ProfileUpdateRequest` con permisos o regla de propio usuario. |
| **ServicePolicy** sin registro / vacía | **Cerrado** en Fase 4: registrada en `AuthServiceProvider` e implementada; `Service::scopeAllowed` y `ServicesController` usan policy/`authorize` donde aplica. |

## Mantenimiento

- Revisar tras cambios de permisos en BD: `php artisan helpdesk:auth-inventory`.
- Si se reintroducen permisos `excel.productos.*` por negocio, alinear rutas y Blade con el mismo criterio.
