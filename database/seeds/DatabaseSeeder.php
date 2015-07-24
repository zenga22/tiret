<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Group;
use Bican\Roles\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        DB::table('group')->delete();
        DB::table('users')->delete();
        DB::table('password_resets')->delete();
        DB::table('rules')->delete();
        DB::table('roles')->delete();
        DB::table('role_user')->delete();
        DB::table('permissions')->delete();
        DB::table('permission_user')->delete();
        DB::table('permission_role')->delete();

        $admin_role = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => ''
        ]);

        $group_admin_role = Role::create([
            'name' => 'Group Admin',
            'slug' => 'groupadmin',
            'description' => ''
        ]);

        $torino = Group::create([
            'name' => 'Torino'
        ]);

        $milano = Group::create([
            'name' => 'Milano'
        ]);

        $u = User::create([
            'name' => 'Amministratore',
            'surname' => 'Generale',
            'username' => 'admin',
            'email' => 'admin@global.com',
            'password' => Hash::make('cippalippa'),
            'group_id' => $torino->id
        ]);

        $u->attachRole($admin_role);

        $u = User::create([
            'name' => 'Amministratore',
            'surname' => 'Torino',
            'username' => 'admin_to',
            'email' => 'admin@local.com',
            'password' => Hash::make('cippalippa'),
            'group_id' => $torino->id
        ]);

        $u->attachRole($group_admin_role);

        Model::reguard();
    }
}
