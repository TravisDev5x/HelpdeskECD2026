## Inventario V2 – Análisis funcional y mejoras pendientes

> Documento interno – resumen de cómo funciona el módulo de **Inventario V2** (activos y componentes) y qué áreas están ya cubiertas vs. qué se puede mejorar.

---

### 1. Visión general del módulo

- **Tecnología**: Laravel 7, Livewire 2, AdminLTE, Bootstrap 4.
- **Componentes principales**:
  - `App\Http\Livewire\Inventory\InventoryIndex` + vista `resources/views/livewire/inventory/inventory-index.blade.php`: listado y gestión de **activos V2**.
  - `App\Models\InvAsset`, `InvCategory`, `InvStatus`, `InvMovement`, `InvMaintenance`, `InvAssetImage`, `InvComponent`.
  - `App\Http\Livewire\Inventory\ComponentIndex` + vista `component-index.blade.php`: gestión de **componentes** asociados a activos.
  - `CategoryManager` / `StatusManager`: catálogos de categorías y estatus de inventario V2.
  - `InventoryExportController` + `InvAssetsExport`: exportación a Excel.
- **Seguridad y acceso**:
  - Rutas bajo `/admin/v2/*` protegidas con `auth`, `password.expiry` y `role:Admin|Soporte|Infraestructura|Mantenimiento|Auditor`.
  - Inventario V2 separado de Inventario V1 (antiguo), conviven ambos.

---

### 2. Qué hace hoy Inventario V2 (activos)

#### 2.1. Listado y filtros

En `InventoryIndex`:

- **Listado tipo “Excel”** con:
  - Etiqueta interna / categoría.
  - Nombre de equipo / serie.
  - Detalles técnicos (marca, modelo, IP, etc.).
  - Ubicación (empresa, sede, ubicación física) y usuario asignado.
  - Estatus (badge configurable por `InvStatus`).
  - Barra de **acciones rápidas**.
- **Filtros y búsqueda**:
  - Texto (por serie, etiqueta interna, IP).
  - Categoría.
  - Estatus.
  - Sede.
  - Paginación (10/25/50/100 por página).
- **Exportación**:
  - Botón “Exportar” → ruta `inventory.export` (controlador `InventoryExportController`).
  - Exporta con los mismos filtros aplicados y columnas: ID, etiqueta, nombre, serie, categoría, estatus, condición, empresa, sede, ubicación, asignado a, costo, garantía hasta, fecha de alta.

#### 2.2. CRUD de activos

En `InventoryIndex` + `InvAsset`:

- **Alta / edición** mediante modal:
  - Datos básicos: nombre, tag interno, serie, categoría, estatus, costo, notas.
  - Ubicación: empresa, sede, ubicación física (dependiente de sede).
  - Especificaciones adicionales: marca, modelo, IP, MAC (guardadas en `specs` JSON).
  - Condición (NUEVO/BUENO/REGULAR/MALO/PARA_PIEZAS).
  - Fechas de compra y garantía.
- **Validaciones**:
  - Campos obligatorios (nombre, categoría, estatus, empresa).
  - `internal_tag` único si se informa.
  - Formato numérico de costo y fechas válidas.
- **Soft-deletes**:
  - `InvAsset` usa `SoftDeletes`; los eliminados se marcan pero se conserva historial.

#### 2.3. Operaciones sobre activos

En `InventoryIndex`:

- **Asignación (CHECKOUT)**:
  - Modal para seleccionar usuario (empleado), con notas de entrega.
  - Actualiza `current_user_id` del activo y estatus según reglas de negocio.
  - Registra un `InvMovement` tipo `CHECKOUT` con usuario implicado, admin, fecha y notas.
- **Devolución (CHECKIN)**:
  - Modal de devolución con notas.
  - Limpia `current_user_id` y actualiza estatus.
  - Registra `InvMovement` tipo `CHECKIN`.
- **Baja**:
  - Modal de baja con selección de estatus de baja y notas.
  - Actualiza estatus, limpia usuario actual.
  - Crea `InvMovement` tipo `BAJA`.
- **Traslado**:
  - Modal para cambiar sede y ubicación.
  - Actualiza `sede_id` y `ubicacion_id`.
  - Crea `InvMovement` tipo `TRASLADO`.
- **Evidencias fotográficas**:
  - Modal para subir fotos (`InvAssetImage`) por activo.
  - Listado y eliminación de imágenes asociadas.
- **Edición masiva**:
  - Selección múltiple (checkbox en tabla) con barra de acciones:
    - Edición masiva de categoría, estatus, empresa, sede y ubicación.
    - Borrado masivo (soft-delete).

#### 2.4. Ficha técnica (Kardex)

La ficha técnica se abre como modal (`viewAsset`):

- **Resumen izquierdo**:
  - Ícono representativo.
  - Tag interno.
  - Estatus (badge).
  - Empresa, sede, ubicación actual.
- **Pestañas derechas**:
  1. **Detalles**:
     - Equipo, serie, costo.
     - Specs dinámicas (marca, modelo, IP, MAC, etc.).
  2. **Historial**:
     - Timeline de `InvMovement` con:
       - Tipo (CHECKOUT / CHECKIN / BAJA / TRASLADO, icono distinto).
       - Fecha.
       - Usuario implicado y/o administrador.
       - Notas (si existen).
  3. **Mantenimientos**:
     - Formulario para registrar mantenimiento:
       - Título, diagnóstico, solución opcional, fecha de inicio, costo.
     - Listado de mantenimientos previos (`InvMaintenance`):
       - Título, fecha, costo, resumen de diagnóstico.

---

### 3. Qué hace hoy Inventario V2 (componentes)

En `ComponentIndex` + modelo `InvComponent`:

- **Listado de componentes**:
  - Nombre, marca, modelo.
  - Serie y capacidad.
  - Estado (STOCK / ASIGNADO / BAJA).
  - Ubicación: activo V2 al que está asociado (internal_tag y nombre del activo).
- **Filtros**:
  - Búsqueda por nombre, serie, marca, equipo.
  - Filtros de estado: Todos / Stock / Asignados / Bajas.
- **CRUD**:
  - Registrar / editar componente (nombre, marca, modelo, serie, capacidad, costo, observaciones).
  - Alta/baja lógica del componente (estado SUSPENDIDO).
  - Asignar componente a un activo V2 (y ver a qué activo está ligado).

---

### 4. Trazabilidad actual

#### 4.1. Activos

- **Spatie Activity Log** en `InvAsset`:
  - Atributos auditados: nombre, tag, serie, categoría, estatus, condición, ubicación completa, costo, fechas, specs, notas.
  - Solo registra cambios “sucios” (`logOnlyDirty`), sin registros vacíos.
  - Se integra con el módulo global de Auditoría (“Seguridad y Logs”).
- **Movimientos** (`InvMovement`):
  - Guarda para cada evento:
    - `asset_id`, `user_id`, `admin_id`, `type`, `date`, `notes`.
  - Relaciona con:
    - `asset()` (con `withTrashed()` para ver históricos de activos dados de baja).
    - `user()` (empleado implicado, con `withTrashed()`).
    - `admin()` (admin que registró).
  - Timeline en la ficha técnica con iconos de tipo de movimiento.
- **Mantenimientos** (`InvMaintenance`):
  - Por activo: `asset_id`, `title`, `diagnosis`, `solution`, `cost`, `start_date`, `end_date`, `logged_by`, `attachments`.
  - Se muestran en tab “Mantenimientos” del Kardex.

**Conclusión**: la trazabilidad de activos es **buena**:
- Quién lo creó / modificó (vía Spatie Activity Log).
- Quién lo usa / ha usado (movimientos CHECKOUT/CHECKIN).
- Dónde ha estado (TRASLADOS).
- Qué mantenimientos se le hicieron, cuándo y con qué costo.

#### 4.2. Componentes

- Estado actual:
  - Se sabe **en qué activo** está el componente (relación `InvAsset::components()` y uso en `ComponentIndex`).
  - Se tiene estado (STOCK / ASIGNADO / BAJA).
- Falta:
  - No existe una tabla de movimientos de componentes (similar a `InvMovement`).
  - No se registra **historial de a qué activos estuvo asignado un componente** ni quién hizo el cambio.

---

### 5. UX / UI y accesibilidad actuales

- **Breadcrumbs**:
  - Usan `partials/breadcrumb` en:
    - Listado de activos.
    - Componentes.
    - Categorías.
- **Accesos directos y acciones**:
  - Botones de acción con iconos y `title` (tooltips) para accesibilidad (Asignar, Devolver, PDF, Traslado, Baja, Fotos, Ficha técnica, Editar, Eliminar).
  - En móvil se usan **menús desplegables** “Acciones” en lugar de muchos iconos pequeños.
- **Mensajes**:
  - Éxito/error mostrados en el `layout` (`session('message')`, `session('error')`) + mensajes más concretos dentro de los componentes.
- **Listados vacíos**:
  - Se usa `partials/empty-state` para mostrar un estado vacío coherente con icono, mensaje y botón de acción.

---

### 6. Qué está bien resuelto

1. **Modelo de datos de activos V2**:
   - Entidades claras: `InvAsset`, `InvCategory`, `InvStatus`, `InvMovement`, `InvMaintenance`, `InvAssetImage`, `InvComponent`.
   - Uso de `SoftDeletes` y logs de actividad.

2. **Trazabilidad del activo**:
   - Movimientos detallados (CHECKOUT, CHECKIN, BAJA, TRASLADO) con timeline en ficha técnica.
   - Historial de mantenimientos con costo y fechas.
   - Auditoría de cambios en atributos clave (Spatie).

3. **Operaciones de negocio clave**:
   - Asignar / devolver equipos.
   - Dar de baja con estatus configurable.
   - Trasladar entre empresas/sedes/ubicaciones.
   - Edición masiva de propiedades relevantes.

4. **Exportación**:
   - Export coherente con filtros activos, columnas útiles para conciliación.

5. **UX básica**:
   - Filtros persistentes con Livewire.
   - Estados vacíos amigables.
   - Menú de acciones adaptado a móvil.

---

### 7. Áreas de mejora detectadas

#### 7.1. Exposición de la auditoría en la propia vista de inventario

- Aunque `InvAsset` está auditado con Spatie, desde el módulo de Inventario V2 el usuario solo ve:
  - Movimientos.
  - Mantenimientos.
- **Propuesta**:
  - Añadir un tab o sección “Cambios” en la ficha técnica que consuma los logs de Spatie para ese activo:
    - Últimos N cambios (quién, cuándo, qué campo cambió, valor anterior/nuevo).
  - Mostrar en el resumen:
    - “Última modificación: usuario / fecha” usando el último event de activity log.

#### 7.2. Trazabilidad de componentes

- Hoy solo se sabe:
  - Estado (STOCK/ASIGNADO/BAJA).
  - A qué activo está actualmente asignado.
- **Faltan**:
  - Histórico de asignaciones de cada componente (a qué equipos se montó y cuándo).
  - Quién hizo los cambios.
- **Propuesta**:
  - Crear `InvComponentMovement` similar a `InvMovement`:
    - `component_id`, `asset_id`, `user_id`, `admin_id`, `type` (ASIGNAR/RETIRAR), `date`, `notes`.
  - Mostrar un timeline de componente en una ficha específica de componente.

#### 7.3. Evidencias fotográficas

- Actualmente:
  - Se guardan fotos por activo (`InvAssetImage`) y se pueden ver/borrar.
  - No se identifica claramente quién subió cada foto ni la fecha de carga en la UI.
- **Propuesta**:
  - Añadir campos `uploaded_by` y `uploaded_at` en `InvAssetImage`.
  - Mostrar en el modal de fotos el usuario y la fecha/hora de carga.
  - Opcional: relacionar evidencias con movimientos (foto de entrega/devolución) enlazando `movement_id`.

#### 7.4. Integración Inventario V2 ↔ Auditoría global

- Hay un módulo de “Seguridad y Logs” para toda la aplicación.
- **Mejora**:
  - Desde la ficha técnica de un activo, ofrecer un enlace directo “Ver en auditoría” que filtre los logs solo de ese activo.

#### 7.5. UX y rendimiento en listados grandes

- Livewire 2 maneja bien el paginado, pero:
  - Con muchos filtros y columnas, la experiencia puede volverse pesada en tablas grandes.
- **Posibles mejoras**:
  - Indexar columnas críticas en la BD (internal_tag, serial, company_id, status_id, sede_id).
  - Considerar filtros “guardados” por usuario (no urgente, pero útil).

---

### 8. Resumen ejecutivo

- **Inventario V2 ya ofrece**:
  - Modelo de datos sólido para activos y componentes.
  - Trazabilidad de activos (movimientos + mantenimientos + logs de Spatie).
  - Operaciones clave (asignaciones, bajas, traslados, edición masiva, export).
  - UX razonable con filtros, estados vacíos y vista de ficha técnica.

- **Principales mejoras recomendadas**:
  1. **Hacer visible la auditoría de cambios (Spatie) en la propia ficha técnica**.
  2. **Extender la trazabilidad a componentes** (historial de asignaciones, similar a movimientos de activo).
  3. **Enriquecer la información de evidencias fotográficas** (quién, cuándo, opcionalmente ligado a movimiento).
  4. **Atajos hacia el módulo de auditoría global** desde Inventario V2 para investigación rápida.

Con estas mejoras, Inventario V2 pasaría de ser un módulo de inventario robusto a un **sistema de gestión de activos con trazabilidad completa** (activo + componentes + evidencias), alineado con necesidades de auditoría interna y compliance.

