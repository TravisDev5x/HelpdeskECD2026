# ADR-001: Convenciones de autorización (roles y permisos)

## Estado

Aceptado — Fase 1 (marzo 2026)

## Contexto

La aplicación usa **Laravel**, **Spatie Permission** y una mezcla de Blade, controladores y **Livewire**. Hace falta un acuerdo estable para que la UI, las rutas y el código de servidor no diverjan.

## Decisión

1. **Servidor como fuente de verdad** para quién puede hacer qué; Blade solo oculta o muestra controles.
2. **Preferir permisos nombrados** frente a comprobar roles en vistas, salvo casos de pertenencia explícita a un grupo.
3. **Livewire**: comprobar permisos en `mount()` y en acciones que cambian datos o exportan.
4. **Policies** para recursos con modelo; **permisos** para acciones transversales sin recurso único.
5. Mantener trazabilidad con el inventario automático: `php artisan helpdesk:auth-inventory`.

## Consecuencias

- Los refactors de pantallas deben revisar ruta + controlador/Livewire, no solo Blade.
- Los FormRequests de formularios sensibles deberán alinearse con estas reglas en fases posteriores (no bloquea este ADR).
