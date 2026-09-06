<?php

use App\Models\Permission;
use App\Models\Role;
use Database\Seeders\RolePermissionSeeder;

test('the role seeder stores the six shop roles and the roles.manage permission', function () {
    $this->seed(RolePermissionSeeder::class);

    $this->assertDatabaseCount('roles', 6);
    $this->assertDatabaseHas('roles', ['slug' => 'owner', 'name' => 'Owner']);
    $this->assertDatabaseHas('permissions', [
        'slug' => 'roles.manage',
        'module' => 'users',
    ]);

    $owner = Role::query()->where('slug', 'owner')->first();
    $admin = Role::query()->where('slug', 'admin')->first();
    $salesperson = Role::query()->where('slug', 'salesperson')->first();

    expect($owner->permissions()->where('slug', 'roles.manage')->exists())->toBeTrue()
        ->and($admin->permissions()->where('slug', 'roles.manage')->exists())->toBeFalse()
        ->and($salesperson->permissions()->where('slug', 'roles.manage')->exists())->toBeFalse()
        ->and(Permission::query()->count())->toBeGreaterThan(3);
});
