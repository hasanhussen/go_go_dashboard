<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class RolesAndAdminSeeder extends Seeder
{
    public function run()
    {
        // // إنشاء Roles إذا مش موجودة
        // $roles = ['admin', 'owner', 'delivery','user'];
        // foreach ($roles as $roleName) {
        //     Role::firstOrCreate(['name' => $roleName]);
        // }



        // إنشاء صلاحيات
Permission::create(['name' => 'add users']);
Permission::create(['name' => 'delete']);
Permission::create(['name' => 'confirm deletion']);
Permission::create(['name' => 'ban']);
Permission::create(['name' => 'approve']);


// إنشاء دور
$adminRole = Role::create(['name' => 'admin']);
$editorRole = Role::create(['name' => 'editor']);
$ownerRole = Role::create(['name' => 'owner']);
$deliveryRole = Role::create(['name' => 'delivery']);
$userRole = Role::create(['name' => 'user']);

// ربط الصلاحيات بالدور
$adminRole->givePermissionTo([
    'add users', 
    'delete',
    'confirm deletion',
    'ban',
    'approve'
]);
$editorRole->givePermissionTo([
    'delete',
    'ban',
    'approve']);

        
        $adminEmail = 'admin@example.com';
        $admin = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Admin',
                'phone'=>'',
                'password' => Hash::make(''),
                'status'=> '1'
            ]
        );

        // إسناد دور الـ admin
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $this->command->info('Roles and Admin user have been created successfully!');
    }
}
