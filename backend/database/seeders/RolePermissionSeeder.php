<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Auto generate permissions first
        \Illuminate\Support\Facades\Artisan::call('shield:generate', ['--all' => true, '--panel' => 'admin', '--ignore-existing-policies' => true]);
        
        // Define Roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $kasirRole = Role::firstOrCreate(['name' => 'kasir', 'guard_name' => 'web']);

        // Give all to Super Admin is managed by Shield usually, but let's give manually or rely on Shield. Let's rely on Shield setting for super_admin.
        
        // Admin Permissions
        $adminRole->syncPermissions([
            // Personal Trainer
            'view_any_personal::trainer', 'create_personal::trainer', 'update_personal::trainer', 'delete_personal::trainer',
            // Member
            'view_any_member', 'create_member', 'update_member', 'delete_member',
            // Account Officer
            'view_any_account::officer', 'create_account::officer', 'update_account::officer', 'delete_account::officer',
            // Promotions
            'view_any_promotion', 'create_promotion', 'update_promotion', 'delete_promotion',
            // Branches - Admin only Read
            'view_any_branch',
            // Payment - Admin only Read
            'view_any_payment',
            // Missing features - Give fully to admin
            'view_any_pt::schedule', 'create_pt::schedule', 'update_pt::schedule', 'delete_pt::schedule',
            'view_any_pt::booking', 'create_pt::booking', 'update_pt::booking', 'delete_pt::booking',
            'view_any_activity::log', 'create_activity::log', 'update_activity::log', 'delete_activity::log',
        ]);

        // Manager Permissions
        $managerRole->syncPermissions([
            'view_any_user',
            'view_any_personal::trainer',
            'view_any_member',
            'view_any_account::officer',
            'view_any_promotion',
            'view_any_branch', 'update_branch', // Manager can update branch
            'view_any_payment',
        ]);

        // Kasir Permissions
        $kasirRole->syncPermissions([
            'view_any_member',
            'view_any_payment', 'create_payment', 'update_payment', 'delete_payment',
        ]);
        
        // Default admin user
        $adminOptions = \App\Models\User::where('email', 'admin@admin.com')->first();
        if ($adminOptions) {
            $adminOptions->assignRole($superAdminRole);
        }
    }
}
