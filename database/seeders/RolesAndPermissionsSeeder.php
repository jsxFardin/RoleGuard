<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        /** @var array{groups: array<int, array{key:string,label:string,modules: array<int, array{key:string,label:string,permissions: array<int, array{slug:string,label:string}>}>}>} $defs */
        $defs = config('permissions');

        foreach (($defs['groups'] ?? []) as $group) {
            foreach (($group['modules'] ?? []) as $module) {
                foreach (($module['permissions'] ?? []) as $perm) {
                    Permission::firstOrCreate(
                        ['slug' => $perm['slug']],
                        ['name' => $module['label'].' - '.$perm['label']],
                    );
                }
            }
        }

        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Admin'],
        );

        $adminRole->permissions()->syncWithoutDetaching(
            Permission::query()->pluck('id')->all(),
        );

        // Assign admin role to the configured email (if exists), otherwise first user.
        $adminEmail = env('ADMIN_EMAIL');
        $user = $adminEmail
            ? User::query()->where('email', $adminEmail)->first()
            : User::query()->orderBy('id')->first();

        if ($user) {
            $user->roles()->syncWithoutDetaching([$adminRole->id]);
        }
    }
}

