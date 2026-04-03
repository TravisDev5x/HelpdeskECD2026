# ADR-002: Capa de aplicación reutilizable (roles / visibilidad)

## Estado

Aceptado — Fase 2 (marzo 2026)

## Contexto

La lógica “si el rol es X entonces filtro Y” estaba repetida en controladores, Livewire y exportaciones, con riesgo de divergencia.

## Decisión

Introducir clases bajo `App\Support\` con responsabilidad única:

| Clase | Responsabilidad |
|-------|-----------------|
| `Authorization\UserPrimaryRole` | Primer rol Spatie (mismo criterio que el legado). |
| `Inventory\ProductOwnerCatalog` | `products.owner`, CTG contenido y exclusiones de reportes entre Mantenimiento/Sistemas. |
| `Tickets\TicketRoleAreaMap` | Mapa rol → `area_id` para tickets. |
| `Tickets\TicketQueryByRole` | Alcance de visibilidad de `Service` (Admin / áreas / solo propios). |

Los controladores y Livewire **delegan** en estas clases; el comportamiento observable debe coincidir con el código anterior.

## Consecuencias

- Jobs, comandos o futuras APIs pueden reutilizar las mismas clases sin copiar `if (role)`.
- Cambios de reglas de negocio deben hacerse en un solo sitio y revisarse con pruebas manuales o automatizadas en los flujos de tickets e inventario.
