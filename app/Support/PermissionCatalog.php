<?php

namespace App\Support;

final class PermissionCatalog
{
    /**
     * @return array{group_key: string, group_label: string, group_sort: int, label: string, description: ?string}
     */
    public static function metadata(string $permissionName): array
    {
        $groups = config('permission_catalog.groups', []);
        $groupKey = self::resolveGroupKey($permissionName);
        $groupDef = $groups[$groupKey] ?? $groups['otros'] ?? ['label' => 'Otros', 'sort' => 999];

        $permConfig = config('permission_catalog.permissions.' . $permissionName, []);

        return [
            'group_key' => $groupKey,
            'group_label' => (string) ($groupDef['label'] ?? 'Otros'),
            'group_sort' => (int) ($groupDef['sort'] ?? 999),
            'label' => (string) ($permConfig['label'] ?? self::defaultLabelFromName($permissionName)),
            'description' => isset($permConfig['description']) ? (string) $permConfig['description'] : null,
        ];
    }

    public static function resolveGroupKey(string $permissionName): string
    {
        foreach (config('permission_catalog.patterns', []) as $row) {
            $pattern = $row['pattern'] ?? '';
            if ($pattern !== '' && @preg_match($pattern, $permissionName)) {
                return (string) ($row['group'] ?? 'otros');
            }
        }

        return 'otros';
    }

    public static function defaultLabelFromName(string $permissionName): string
    {
        return self::translateWords(str_replace(['.', '_'], ' ', $permissionName));
    }

    private static function translateWords(string $text): string
    {
        $dictionary = [
            'create' => 'Crear',
            'read' => 'Ver',
            'update' => 'Actualizar',
            'delete' => 'Eliminar',
            'remove' => 'Remover',
            'check' => 'Validar',
            'modulo' => 'Módulo',
            'module' => 'Módulo',
            'user' => 'Usuario',
            'users' => 'Usuarios',
            'role' => 'Rol',
            'roles' => 'Roles',
            'permission' => 'Permiso',
            'permissions' => 'Permisos',
            'campaign' => 'Campaña',
            'campaigns' => 'Campañas',
            'department' => 'Departamento',
            'departments' => 'Departamentos',
            'position' => 'Puesto',
            'positions' => 'Puestos',
            'area' => 'Área',
            'areas' => 'Áreas',
            'sede' => 'Sede',
            'sedes' => 'Sedes',
            'service' => 'Servicio',
            'services' => 'Servicios',
            'failure' => 'Falla',
            'failures' => 'Fallas',
            'incident' => 'Incidente',
            'incidents' => 'Incidentes',
            'asset' => 'Activo',
            'assets' => 'Activos',
            'assignment' => 'Asignación',
            'assignments' => 'Asignaciones',
            'assignmentsindividual' => 'Asignaciones (individual)',
            'company' => 'Compañía',
            'companies' => 'Compañías',
            'calendar' => 'Calendario',
            'chat' => 'Chat',
            'bitacora' => 'Bitácora',
            'bitacorashost' => 'Bitácora host',
            'bitacoras' => 'Bitácoras',
            'test' => 'Prueba',
            'tests' => 'Pruebas',
            'report' => 'Reporte',
            'reports' => 'Reportes',
            'ticket' => 'Ticket',
            'inventory' => 'Inventario',
            'monitor' => 'Monitoreo',
            'manage' => 'Gestionar',
            'config' => 'Configuración',
            'maintenance' => 'Mantenimiento',
            'catalogs' => 'Catálogos',
            'did' => 'DID',
            'ubicaciones' => 'Ubicaciones',
            'activo' => 'Activo',
            'activos' => 'Activos',
            'certification' => 'Certificación',
            'edit' => 'Editar',
            'descarga' => 'Descarga',
            'productosall' => 'productos (todos)',
            'panel' => 'Panel',
            'notifications' => 'Notificaciones',
        ];

        $parts = preg_split('/\s+/', trim($text)) ?: [];
        $translated = array_map(function ($word) use ($dictionary) {
            $key = mb_strtolower(trim($word));

            return $dictionary[$key] ?? ucfirst($word);
        }, $parts);

        return trim(implode(' ', $translated));
    }
}
