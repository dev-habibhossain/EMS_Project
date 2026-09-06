<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

test('a user with roles.manage can authorize that ability', function () {
    $role = Role::factory()->create(['slug' => 'admin']);
    $permission = Permission::factory()->create([
        'slug' => 'roles.manage',
        'module' => 'users',
    ]);
    $role->permissions()->attach($permission);

    $user = User::factory()->create(['role_id' => $role->id]);

    expect($user->hasPermission('roles.manage'))->toBeTrue()
        ->and($user->can('roles.manage'))->toBeTrue()
        ->and($user->permissionSlugs())->toContain('roles.manage');
});

test('a user without roles.manage cannot authorize that ability', function () {
    $role = Role::factory()->create(['slug' => 'sales_shop']);
    $permission = Permission::factory()->create([
        'slug' => 'pos.use',
        'module' => 'sales',
    ]);
    $role->permissions()->attach($permission);

    $user = User::factory()->create(['role_id' => $role->id]);

    expect($user->hasPermission('roles.manage'))->toBeFalse()
        ->and($user->can('roles.manage'))->toBeFalse()
        ->and($user->hasPermission('pos.use'))->toBeTrue();
});

test('an admin user is identified by the admin role slug', function () {
    $role = Role::factory()->create(['slug' => 'admin']);
    $user = User::factory()->create(['role_id' => $role->id]);

    expect($user->isAdmin())->toBeTrue()
        ->and($user->isSalesShop())->toBeFalse();
});

test('a sales shop user is identified by the sales shop role slug', function () {
    $role = Role::factory()->create(['slug' => 'sales_shop']);
    $user = User::factory()->create(['role_id' => $role->id]);

    expect($user->isSalesShop())->toBeTrue()
        ->and($user->isAdmin())->toBeFalse();
});

test('a user without a role has no permissions', function () {
    $user = User::factory()->create();

    expect($user->hasPermission('roles.manage'))->toBeFalse()
        ->and($user->can('roles.manage'))->toBeFalse()
        ->and($user->permissionSlugs())->toBe([]);
});
