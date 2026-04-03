<?php

/**
 * Catálogo de permisos: agrupa y etiqueta para la matriz en UI (roles / listado).
 * El nombre técnico en BD no cambia; aquí solo mejora claridad para quien asigna permisos.
 */
return [
    'groups' => [
        'seguridad' => ['label' => 'Seguridad: roles y permisos', 'sort' => 5],
        'usuarios' => ['label' => 'Usuarios', 'sort' => 10],
        'organizacion' => ['label' => 'Organización (deptos., puestos, áreas, fallas)', 'sort' => 20],
        'servicios' => ['label' => 'Tickets y servicios', 'sort' => 30],
        'inventario_v2' => ['label' => 'Inventario V2 (activos Livewire)', 'sort' => 35],
        'inventario_v1' => ['label' => 'Inventario V1 (legacy)', 'sort' => 36],
        'asignaciones' => ['label' => 'Asignaciones de equipos', 'sort' => 40],
        'reportes' => ['label' => 'Reportes', 'sort' => 50],
        'empresas_catalogos' => ['label' => 'Empresas, campañas y catálogos', 'sort' => 60],
        'bitacora' => ['label' => 'Bitácoras', 'sort' => 70],
        'activos_certificacion' => ['label' => 'Activos, pruebas e incidencias', 'sort' => 80],
        'modulos' => ['label' => 'Módulos (DID, ubicaciones, calendario)', 'sort' => 90],
        'notificaciones' => ['label' => 'Notificaciones internas (campana)', 'sort' => 95],
        'otros' => ['label' => 'Otros', 'sort' => 999],
    ],

    'patterns' => [
        ['group' => 'seguridad', 'pattern' => '/^(create|read|update|delete) (role|roles|permission|permissions)$/'],
        ['group' => 'reportes', 'pattern' => '/^read reports/'],
        ['group' => 'inventario_v2', 'pattern' => '/^(read|edit|manage|use) inventory\b/'],
        ['group' => 'asignaciones', 'pattern' => '/assignment/'],
        ['group' => 'inventario_v1', 'pattern' => '/product/'],
        // Antes que «usuarios» (\buser), para no agrupar receive internal notification user * en Usuarios.
        ['group' => 'notificaciones', 'pattern' => '/^receive internal notification\b/'],
        ['group' => 'usuarios', 'pattern' => '/\buser/'],
        ['group' => 'organizacion', 'pattern' => '/\b(department|position|area|failure)/'],
        ['group' => 'servicios', 'pattern' => '/\bservice/'],
        ['group' => 'empresas_catalogos', 'pattern' => '/\b(company|campaign)/'],
        ['group' => 'bitacora', 'pattern' => '/bitacora/'],
        ['group' => 'activos_certificacion', 'pattern' => '/\b(asset|test|incident|certification)/'],
        ['group' => 'modulos', 'pattern' => '/^(modulo\.|read calendar|read team calendar|manage team calendar)/'],
        ['group' => 'otros', 'pattern' => '/.*/'],
    ],

    'permissions' => [
        'read inventory own assignments' => [
            'label' => 'Inventario V2: solo mis equipos asignados',
            'description' => 'Ver activos cuyo responsable eres tú; no permite ver ni filtrar por otros usuarios. Suele darse a todos los roles.',
        ],
        'read inventory' => [
            'label' => 'Inventario V2: ver y operar vistas',
            'description' => 'Listado, mantenimientos, pendientes, asignaciones, componentes y exportación de inventario. No incluye monitor ni catálogos de configuración.',
        ],
        'read inventory assignment history' => [
            'label' => 'Inventario V2: historial de asignaciones (solo lectura)',
            'description' => 'Pantalla `/v2/historial-asignaciones` y export CSV de la bitácora (inv_movements). Puede otorgarse sin «read inventory» para perfiles de auditoría.',
        ],
        'edit inventory' => [
            'label' => 'Inventario V2: editar activos y movimientos',
            'description' => 'Registrar cambios masivos, movimientos y operaciones que modifican datos de activos.',
        ],
        'read inventory monitor' => [
            'label' => 'Inventario V2: monitoreo y exportes KPI',
            'description' => 'Tablero de KPIs, alertas y descargas Excel del monitor. En el menú vive bajo Reportes (no en el árbol Inventario V2). Independiente de «read inventory».',
        ],
        'manage inventory config' => [
            'label' => 'Inventario V2: categorías y estatus',
            'description' => 'Administración de catálogos maestros de categorías y estatus de activos.',
        ],
        'manage inventory labels' => [
            'label' => 'Inventario V2: etiquetas por sede',
            'description' => 'Administración del catálogo de etiquetas vinculado a sedes para autocompletar en activos de Inventario V2.',
        ],
        'manage inventory maintenance catalogs' => [
            'label' => 'Inventario V2: catálogos de mantenimiento',
            'description' => 'Orígenes y modalidades de mantenimiento (interno/externo, preventivo/correctivo, etc.).',
        ],
        'use inventory filter search' => [
            'label' => 'Inventario V2: filtro búsqueda',
            'description' => 'Permite usar el filtro por texto (etiqueta, serie, nombre) en vistas de Inventario V2.',
        ],
        'use inventory filter category' => [
            'label' => 'Inventario V2: filtro categoría',
            'description' => 'Permite filtrar por categoría de activo.',
        ],
        'use inventory filter status' => [
            'label' => 'Inventario V2: filtro estatus',
            'description' => 'Permite filtrar por estatus de activo.',
        ],
        'use inventory filter assignee' => [
            'label' => 'Inventario V2: filtro responsable',
            'description' => 'Permite filtrar por usuario responsable/asignado.',
        ],
        'use inventory filter sede' => [
            'label' => 'Inventario V2: filtro sede',
            'description' => 'Permite filtrar por sede.',
        ],
        'use inventory filter assignee employment' => [
            'label' => 'Inventario V2: filtro estatus laboral',
            'description' => 'Permite filtrar responsables por estatus laboral en pantallas de asignaciones.',
        ],
        'use inventory filter monitor range' => [
            'label' => 'Inventario V2 Monitor: filtro rango',
            'description' => 'Permite cambiar rango rápido de tiempo en monitor.',
        ],
        'use inventory filter monitor dates' => [
            'label' => 'Inventario V2 Monitor: filtro fechas',
            'description' => 'Permite definir fecha inicio/fin manualmente en monitor.',
        ],
        'use inventory filter monitor company' => [
            'label' => 'Inventario V2 Monitor: filtro empresa',
            'description' => 'Permite filtrar monitor por empresa.',
        ],
        'use inventory filter monitor sede' => [
            'label' => 'Inventario V2 Monitor: filtro sede',
            'description' => 'Permite filtrar monitor por sede.',
        ],
        'use inventory filter monitor search' => [
            'label' => 'Inventario V2 Monitor: filtro búsqueda',
            'description' => 'Permite buscar activos dentro del monitor.',
        ],
        'use inventory filter monitor event type' => [
            'label' => 'Inventario V2 Monitor: filtro tipo de evento',
            'description' => 'Permite limitar la línea de tiempo a movimientos, mantenimientos o cambios.',
        ],
        'read reports' => [
            'label' => 'Reportes: acceder al menú',
            'description' => 'Despliega la sección de reportes; suelen requerirse además permisos hijos (tickets o inventario).',
        ],
        'read reports ticket' => [
            'label' => 'Reportes: tickets',
            'description' => 'Informes relacionados con tickets.',
        ],
        'escalate service' => [
            'label' => 'Tickets: escalar a otra área',
            'description' => 'Cambiar el tipo de falla (área de servicio) de un ticket; queda registro en historial. Quien solo crea tickets (p. ej. General) no lo tiene por defecto.',
        ],
        'read reports inventory' => [
            'label' => 'Reportes: detalle inventario (clásico)',
            'description' => 'Reportes de inventario del módulo administrativo; distinto del inventario V2 Livewire.',
        ],
        'read calendar' => [
            'label' => 'Agenda: acceder al calendario',
            'description' => 'Abre el módulo de agenda y gestiona el calendario personal (sus propios eventos).',
        ],
        'read team calendar' => [
            'label' => 'Agenda: ver calendario de equipo',
            'description' => 'Ve en la misma vista los eventos marcados como de equipo (compartidos), además de los personales.',
        ],
        'manage team calendar' => [
            'label' => 'Agenda: gestionar eventos de equipo',
            'description' => 'Crear y editar eventos de equipo visibles para quien tenga «ver calendario de equipo». Sin este permiso solo puede gestionar sus eventos personales.',
        ],
        'receive internal notification ticket created' => [
            'label' => 'Notif. interna: nuevo ticket',
            'description' => 'Recibe en la campana el aviso cuando se crea un ticket (y el correo paralelo si tiene email). Quien no lo tenga no entra en la lista de destinatarios.',
        ],
        'receive internal notification user login' => [
            'label' => 'Notif. interna: inicio de sesión',
            'description' => 'Recibe en la campana el aviso cuando otro usuario inicia sesión (auditoría). No se notifica a uno mismo.',
        ],
        'receive internal notification password support' => [
            'label' => 'Notif. interna: solicitud ayuda acceso / contraseña',
            'description' => 'Recibe el aviso cuando alguien usa el formulario de apoyo desde recuperación de acceso.',
        ],
        'receive internal notification user missing email' => [
            'label' => 'Notif. interna: usuario sin correo',
            'description' => 'Recibe aviso cuando un usuario entra o restablece sesión sin email en su perfil (regularizar cuenta).',
        ],
        'receive internal notification ticket assigned' => [
            'label' => 'Notif. interna: ticket asignado a mí',
            'description' => 'Recibe aviso en la campana cuando te asignan como responsable de un ticket.',
        ],
        'receive internal notification ticket resolved' => [
            'label' => 'Notif. interna: mi ticket resuelto',
            'description' => 'Recibe aviso cuando un ticket tuyo pasa a resuelto (finalizado).',
        ],
        'receive internal notification ticket closed' => [
            'label' => 'Notif. interna: mi ticket cerrado (otro estatus)',
            'description' => 'Recibe aviso cuando un ticket tuyo se cierra con un estatus distinto de resuelto.',
        ],
        'receive internal notification ticket requester alert' => [
            'label' => 'Notif. interna: alerta del solicitante en ticket',
            'description' => 'Recibe en la campana el aviso cuando el solicitante marca «enviar alerta» al añadir una nota visible. Por defecto se asigna a Soporte, Infraestructura, Telecomunicaciones y Mantenimiento.',
        ],
        'receive internal notification password expiring soon' => [
            'label' => 'Notif. interna: contraseña por vencer',
            'description' => 'Recibe el aviso del panel (y comando helpdesk:notify-password-expiry) cuando tu contraseña está próxima a caducar.',
        ],
        'receive internal notification info' => [
            'label' => 'Notif. interna: genéricas (tipo info)',
            'description' => 'Notificaciones internas con tipo genérico «info» (reservado para extensiones). Por defecto suele asignarse solo a administración.',
        ],
    ],
];
