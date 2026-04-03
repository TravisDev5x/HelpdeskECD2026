# Viabilidad de migrar el proyecto HelpDesk a Laravel 12

## Respuesta corta

**Sí es viable**, pero es una migración **grande y gradual**. No se puede pasar de Laravel 7 a 12 de un salto; hay que subir versión por versión y tocar dependencias, PHP y bastante código. Conviene planificarla por fases y con pruebas en cada paso.

---

## Requisitos actuales vs Laravel 12

| Aspecto | Proyecto actual (Laravel 7) | Laravel 12 |
|--------|------------------------------|------------|
| **PHP** | ^7.2.5 \| ^8.0 | **PHP 8.2+** (hasta 8.5) |
| **Laravel** | ^7.29 | ^12.x |
| **Livewire** | 2.0 | Livewire 3.x (cambios de sintaxis y namespace) |

---

## Ruta de migración recomendada

Laravel no permite saltar versiones mayores. La ruta típica es:

1. **Laravel 7 → 8** (PHP 7.3+ / 8.0, cambios de estructura y paquetes)
2. **Laravel 8 → 9** (PHP 8.0+, Flysystem, etc.)
3. **Laravel 9 → 10** (PHP 8.1+)
4. **Laravel 10 → 11** (PHP 8.2+, cambios de estructura de app)
5. **Laravel 11 → 12** (PHP 8.2+, pocos breaking si ya estás en 11)

En cada paso hay que seguir la guía oficial de upgrade y corregir lo que marque.

---

## Puntos críticos de este proyecto

### 1. **Livewire 2 → 3**

- Uso intenso de Livewire (inventario V2, componentes, categorías, estatus, auditoría, sesiones).
- En Livewire 3:
  - Namespace recomendado: `App\Http\Livewire` → `App\Livewire`.
  - `wire:model` por defecto es “deferred” (equivalente al antiguo `.defer`).
  - Cambios en eventos, tests y en cómo se inyectan estilos/scripts.
- Existe el comando `php artisan livewire:upgrade` y herramientas como [Laravel Shift (Livewire 2→3)](https://laravelshift.com/upgrade-livewire-2-to-livewire-3) para automatizar parte del trabajo.

### 2. **Dependencias en `composer.json`**

| Paquete | Notas para Laravel 11/12 |
|---------|---------------------------|
| `laravel/ui` | Sustituir o usar versión compatible con Laravel 11/12 (o migrar a Breeze/Jetstream si se desea). |
| `fruitcake/laravel-cors` | CORS viene integrado en Laravel desde 9; eliminar y usar config nativa. |
| `fideloper/proxy` | En Laravel 8+ se usa `TrustProxies` nativo; revisar y ajustar. |
| `spatie/laravel-permission` | Usar versión compatible con Laravel 12 (y PHP 8.2+). |
| `spatie/laravel-activitylog` | Idem; revisar compatibilidad en cada salto de Laravel. |
| `yajra/laravel-datatables-oracle` | Actualizar a versión que soporte Laravel 11/12. |
| `maatwebsite/excel` | Actualizar a versión compatible. |
| `livewire/livewire` | Pasar a ^3.0 y seguir guía de migración Livewire 3. |
| `barryvdh/laravel-dompdf` | Actualizar a versión compatible. |
| `intervention/image` | Revisar si hay v3 y compatibilidad con PHP 8.2+. |

### 3. **Estructura de la aplicación**

- En **Laravel 11** cambia la estructura (menos config por defecto, `bootstrap/app.php`, etc.). Al llegar a 11 hay que adaptar la app a esa estructura.
- `app/Http/Kernel.php` en Laravel 11 pasa a configurarse desde `bootstrap/app.php`; los middlewares se registran ahí.

### 4. **Código propio**

- **PHP 8.2+**: eliminar código deprecado (por ejemplo, variables dinámicas en `$$var`), y revisar tipos y atributos.
- **Blade**: revisar directivas y sintaxis según la guía de cada versión.
- **Rutas**: en Laravel 11+ la definición de rutas y middlewares puede cambiar; revisar `routes/web.php` y `bootstrap/app.php`.
- **Sesiones**: ya usas `sessions` en BD y lógica de inactividad; mantener esa lógica y adaptarla a la nueva configuración de sesión y middlewares.

---

## Estimación de esfuerzo (orientativa)

| Fase | Tarea | Esfuerzo relativo |
|------|--------|--------------------|
| 1 | PHP 8.0 → 8.2 en entorno local/CI y pruebas | Bajo |
| 2 | Laravel 7 → 8 (guía oficial + dependencias) | Medio–Alto |
| 3 | Laravel 8 → 9 | Medio |
| 4 | Laravel 9 → 10 | Medio |
| 5 | Laravel 10 → 11 (estructura y Kernel) | Alto |
| 6 | Laravel 11 → 12 | Bajo–Medio |
| 7 | Livewire 2 → 3 (vistas, componentes, tests) | Alto |
| 8 | Pruebas E2E, regresión y ajustes | Alto |

Dependiendo del tamaño del equipo y de las pruebas automatizadas, puede ir desde **varias semanas** hasta **varios meses**.

---

## Recomendaciones

1. **Migrar por etapas**: una versión mayor de Laravel a la vez, con tests y despliegue en un entorno de staging.
2. **PHP primero**: subir a PHP 8.2 en desarrollo y CI, corregir deprecaciones y fallos antes de tocar Laravel.
3. **Usar las guías oficiales**: [Laravel Upgrade Guide](https://laravel.com/docs/upgrade) para cada versión (8, 9, 10, 11, 12).
4. **Livewire al final**: hacer la migración de Livewire 2 → 3 cuando ya estés en Laravel 11 o 12, y usar `livewire:upgrade` + revisión manual.
5. **Herramientas de ayuda**: valorar [Laravel Shift](https://laravelshift.com/) para automatizar cambios de código en cada upgrade.
6. **Backups y control de versiones**: backup de BD y código, y ramas por cada paso de migración para poder volver atrás.

---

## Conclusión

- **¿Es viable?** Sí.
- **¿Es directo?** No: exige subir PHP, Laravel paso a paso y Livewire 2 → 3, además de actualizar varias dependencias.
- **Recomendación**: planificar la migración por fases (Laravel 7→8→9→10→11→12 y luego Livewire 3), con pruebas y staging en cada fase, y priorizar primero PHP 8.2 y Laravel 8/9 para ir ganando seguridad y compatibilidad sin intentar llegar a 12 de un solo paso.
