<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'roles.ver',
            'roles.crear',
            'roles.editar',
            'roles.eliminar',
            'permisos.ver',
            'permisos.crear',
            'permisos.editar',
            'permisos.eliminar',
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.eliminar',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'api');
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $admin = Role::findOrCreate('Administrador', 'api');
        $admin->syncPermissions($permissions);

        Role::findOrCreate('Usuario', 'api');

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@epita-unap.test'],
            ['name' => 'Administrador', 'password' => bcrypt('password')],
        );

        $adminUser->assignRole($admin);
    }
}
