<?php

use App\Models\Permission;
use App\Models\Role;
use Database\Seeders\RolePermissionSeeder;

test('the role seeder stores admin and sales shop with mandatory and recommended grants', function () {
    $this->seed(RolePermissionSeeder::class);

    $this->assertDatabaseCount('roles', 2);
    $this->assertDatabaseHas('roles', ['slug' => 'admin', 'name' => 'Admin']);
    $this->assertDatabaseHas('roles', ['slug' => 'sales_shop', 'name' => 'Sales shop']);
    $this->assertDatabaseHas('permissions', [
        'slug' => 'roles.manage',
        'module' => 'users',
    ]);

    $admin = Role::query()->where('slug', 'admin')->first();
    $salesShop = Role::query()->where('slug', 'sales_shop')->first();

    expect($admin->permissions()->where('slug', 'roles.manage')->exists())->toBeTrue()
        ->and($admin->permissions()->where('slug', 'purchases.create')->exists())->toBeTrue()
        ->and($admin->permissions()->where('slug', 'users.manage')->exists())->toBeTrue()
        ->and($salesShop->permissions()->where('slug', 'pos.use')->exists())->toBeTrue()
        ->and($salesShop->permissions()->where('slug', 'challans.create')->exists())->toBeTrue()
        ->and($salesShop->permissions()->where('slug', 'purchases.view')->exists())->toBeFalse()
        ->and($salesShop->permissions()->where('slug', 'users.manage')->exists())->toBeFalse()
        ->and($salesShop->permissions()->where('slug', 'roles.manage')->exists())->toBeFalse()
        ->and(Permission::query()->count())->toBeGreaterThan(3);
});
