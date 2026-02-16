<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Roles globais (tenant_id = null)
        $roles = ['owner', 'admin', 'staff'];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['tenant_id' => null, 'name' => $role],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // Permissions globais mínimas
        $permissions = [
            ['key' => 'users.manage',   'description' => 'Gerenciar usuários'],
            ['key' => 'tenants.manage', 'description' => 'Gerenciar tenants'],
            ['key' => 'vehicles.manage','description' => 'Gerenciar veículos'],
            ['key' => 'reports.view',   'description' => 'Visualizar relatórios'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['key' => $permission['key']],
                [
                    'description' => $permission['description'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }
    }
}
