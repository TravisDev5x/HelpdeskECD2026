<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        app()['cache']->forget('spatie.permission.cache');

        Permission::create(['name' => 'create user']);
        Permission::create(['name' => 'read users']);
        Permission::create(['name' => 'update user']);
        Permission::create(['name' => 'delete user']);

        Permission::create(['name' => 'create department']);
        Permission::create(['name' => 'read departments']);
        Permission::create(['name' => 'update department']);
        Permission::create(['name' => 'delete department']);

        Permission::create(['name' => 'create position']);
        Permission::create(['name' => 'read positions']);
        Permission::create(['name' => 'update position']);
        Permission::create(['name' => 'delete position']);

        Permission::create(['name' => 'create area']);
        Permission::create(['name' => 'read areas']);
        Permission::create(['name' => 'update area']);
        Permission::create(['name' => 'delete area']);

        Permission::create(['name' => 'create failure']);
        Permission::create(['name' => 'read failures']);
        Permission::create(['name' => 'update failure']);
        Permission::create(['name' => 'update failures']);
        Permission::create(['name' => 'delete failure']);

        Permission::create(['name' => 'create service']);
        Permission::create(['name' => 'read services']);
        Permission::create(['name' => 'update service']);
        Permission::firstOrCreate(
            ['name' => 'escalate service', 'guard_name' => config('auth.defaults.guard', 'web')]
        );

        Permission::create(['name' => 'create product']);
        Permission::create(['name' => 'read products']);
        Permission::create(['name' => 'descarga_productosall']);
        Permission::create(['name' => 'update product']);
        Permission::create(['name' => 'delete product']);

        Permission::create(['name' => 'create role']);
        Permission::create(['name' => 'read roles']);
        Permission::create(['name' => 'update role']);
        Permission::create(['name' => 'delete role']);

        Permission::create(['name' => 'create permission']);
        Permission::create(['name' => 'read permissions']);
        Permission::create(['name' => 'update permission']);
        Permission::create(['name' => 'delete permission']);

        Permission::create(['name' => 'read reports']);
        Permission::create(['name' => 'read reports ticket']);
        Permission::create(['name' => 'read reports inventory']);

        Permission::create(['name' => 'read assignments']);
        Permission::create(['name' => 'remove assignments']);
        Permission::create(['name' => 'delete assignment']);

        Permission::create(['name' => 'create company']);
        Permission::create(['name' => 'read companies']);
        Permission::create(['name' => 'update company']);
        Permission::create(['name' => 'delete company']);

        Permission::create(['name' => 'create campaign']);
        Permission::create(['name' => 'read campaigns']);
        Permission::create(['name' => 'update campaign']);
        Permission::create(['name' => 'delete campaign']);

        Permission::create(['name' => 'read tests']);
        Permission::create(['name' => 'create test']);

        Permission::create(['name' => 'read assets']);
        Permission::create(['name' => 'create asset']);
        Permission::create(['name' => 'update asset']);

        Permission::create(['name' => 'read incidents']);
        Permission::create(['name' => 'create incident']);
        Permission::create(['name' => 'update incident']);

        Permission::create(['name' => 'create bitacoras']);
        Permission::create(['name' => 'read bitacoras']);

        Permission::create(['name' => 'create bitacorasHost']);
        Permission::create(['name' => 'read bitacorasHost']);

        Permission::create(['name' => 'edit certification']);

        Permission::create(['name' => 'check activos']);

        Permission::create(['name' => 'read assignmentsIndividual']);

        Permission::create(['name' => 'modulo.did']);
        Permission::create(['name' => 'modulo.ubicaciones']);
        Permission::create(['name' => 'read calendar']);

        foreach ([
            'receive internal notification ticket created',
            'receive internal notification user login',
            'receive internal notification password support',
            'receive internal notification user missing email',
            'receive internal notification ticket assigned',
            'receive internal notification ticket resolved',
            'receive internal notification ticket closed',
            'receive internal notification password expiring soon',
            'receive internal notification info',
        ] as $internalNotifPerm) {
            Permission::firstOrCreate(
                ['name' => $internalNotifPerm, 'guard_name' => config('auth.defaults.guard', 'web')]
            );
        }

        $role = Role::create(['name' => 'Admin']);
        $role->givePermissionTo(Permission::all());

        $role = Role::create(['name' => 'Suspendido']);

        $role = Role::create(['name' => 'General']);
        $role->givePermissionTo('create service');
        $role->givePermissionTo('read services');

        $role->givePermissionTo('read assignmentsIndividual');

        $role = Role::create(['name' => 'Soporte']);
        $role->givePermissionTo('create user');
        $role->givePermissionTo('read users');
        $role->givePermissionTo('update user');

        $role->givePermissionTo('create department');
        $role->givePermissionTo('read departments');
        $role->givePermissionTo('update department');

        $role->givePermissionTo('create position');
        $role->givePermissionTo('read positions');
        $role->givePermissionTo('update position');

        $role->givePermissionTo('create area');
        $role->givePermissionTo('read areas');
        $role->givePermissionTo('update area');

        $role->givePermissionTo('create failure');
        $role->givePermissionTo('read failures');
        $role->givePermissionTo('update failure');
        $role->givePermissionTo('update failures');

        $role->givePermissionTo('create service');
        $role->givePermissionTo('read services');
        $role->givePermissionTo('update service');
        $role->givePermissionTo('escalate service');

        $role->givePermissionTo('check activos');

        $role->givePermissionTo('create bitacoras');
        $role->givePermissionTo('read bitacoras');

        $role->givePermissionTo('read assignmentsIndividual');

        $role->givePermissionTo([
            'receive internal notification password support',
            'receive internal notification user missing email',
        ]);

        $role = Role::create(['name' => 'Metricas']);
        $role->givePermissionTo('create service');
        $role->givePermissionTo('read services');
        $role->givePermissionTo('update service');
        $role->givePermissionTo('escalate service');

        $role->givePermissionTo('create failure');
        $role->givePermissionTo('read failures');
        $role->givePermissionTo('update failure');

        $role->givePermissionTo('create test');
        $role->givePermissionTo('read tests');

        $role->givePermissionTo('create asset');
        $role->givePermissionTo('read assets');

        $role->givePermissionTo('create incident');
        $role->givePermissionTo('read incidents');
        $role->givePermissionTo('update incident');

        $role->givePermissionTo('create bitacoras');
        $role->givePermissionTo('read bitacoras');

        $role->givePermissionTo('read assignmentsIndividual');

        $role = Role::create(['name' => 'Telecominicaciones']);
        $role->givePermissionTo('create service');
        $role->givePermissionTo('read services');
        $role->givePermissionTo('update service');
        $role->givePermissionTo('escalate service');

        $role->givePermissionTo('create failure');
        $role->givePermissionTo('read failures');
        $role->givePermissionTo('update failure');

        $role->givePermissionTo('create test');
        $role->givePermissionTo('read tests');

        $role->givePermissionTo('create asset');
        $role->givePermissionTo('read assets');
        $role->givePermissionTo('update asset');

        $role->givePermissionTo('create incident');
        $role->givePermissionTo('read incidents');
        $role->givePermissionTo('update incident');

        $role->givePermissionTo('create bitacoras');
        $role->givePermissionTo('read bitacoras');

        $role->givePermissionTo('read assignmentsIndividual');

        $role = Role::create(['name' => 'Basico']);

        $role = Role::create(['name' => 'Recursos Humanos']);
        $role->givePermissionTo('create user');
        $role->givePermissionTo('read users');
        $role->givePermissionTo('update user');
        $role->givePermissionTo('delete user');

        $role->givePermissionTo('create department');
        $role->givePermissionTo('read departments');
        $role->givePermissionTo('update department');
        $role->givePermissionTo('delete department');

        $role->givePermissionTo('create service');
        $role->givePermissionTo('read services');

        $role->givePermissionTo('create campaign');
        $role->givePermissionTo('read campaigns');
        $role->givePermissionTo('update campaign');
        $role->givePermissionTo('delete campaign');

        $role->givePermissionTo('edit certification');

        $role->givePermissionTo('create bitacoras');
        $role->givePermissionTo('read bitacoras');

        $role->givePermissionTo('read assignmentsIndividual');

        $role = Role::create(['name' => 'Finanzas']);
        $role->givePermissionTo('create service');
        $role->givePermissionTo('read services');

        $role->givePermissionTo('read products');

        $role->givePermissionTo('read assignments');

        $role->givePermissionTo('read assignmentsIndividual');

        $role = Role::create(['name' => 'IH']);
        $role->givePermissionTo('create user');
        $role->givePermissionTo('read users');
        $role->givePermissionTo('update user');

        $role->givePermissionTo('read assignmentsIndividual');

        $role = Role::create(['name' => 'Supervisor']);
        $role->givePermissionTo('create service');
        $role->givePermissionTo('read services');

        $role->givePermissionTo('read assignmentsIndividual');

        $role = Role::create(['name' => 'Auditor']);
        $role->givePermissionTo('read products');
        $role->givePermissionTo('update products');

        $guard = config('auth.defaults.guard', 'web');
        $pwdSoonPerm = Permission::where('name', 'receive internal notification password expiring soon')->where('guard_name', $guard)->first();
        $assignedPerm = Permission::where('name', 'receive internal notification ticket assigned')->where('guard_name', $guard)->first();
        $resolvedPerm = Permission::where('name', 'receive internal notification ticket resolved')->where('guard_name', $guard)->first();
        $closedPerm = Permission::where('name', 'receive internal notification ticket closed')->where('guard_name', $guard)->first();
        foreach (Role::query()->cursor() as $roleModel) {
            if ($roleModel->name === 'Admin' || $roleModel->name === 'Suspendido') {
                continue;
            }
            if ($pwdSoonPerm && ! $roleModel->hasPermissionTo($pwdSoonPerm)) {
                $roleModel->givePermissionTo($pwdSoonPerm);
            }
            if ($assignedPerm && $roleModel->hasPermissionTo('update service') && ! $roleModel->hasPermissionTo($assignedPerm)) {
                $roleModel->givePermissionTo($assignedPerm);
            }
            if ($roleModel->hasPermissionTo('create service') || $roleModel->hasPermissionTo('read services')) {
                if ($resolvedPerm && ! $roleModel->hasPermissionTo($resolvedPerm)) {
                    $roleModel->givePermissionTo($resolvedPerm);
                }
                if ($closedPerm && ! $roleModel->hasPermissionTo($closedPerm)) {
                    $roleModel->givePermissionTo($closedPerm);
                }
            }
        }

        $panelNotifications = Permission::firstOrCreate(
            ['name' => 'read panel notifications', 'guard_name' => 'web']
        );
        foreach (Role::query()->get() as $roleModel) {
            if ($roleModel->name === 'Admin' || $roleModel->hasPermissionTo('read services')) {
                $roleModel->givePermissionTo($panelNotifications);
            }
        }

        app()['cache']->forget('spatie.permission.cache');
    }
}
