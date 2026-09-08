<?php

use App\Models\Brand;
use App\Models\Factory;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests cannot view brands page', function (): void {
    $this->get(route('brands.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users can view brands index page with pagination and data', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('brands.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Brands/Index')
            ->has('brands.data')
            ->has('brands.current_page')
            ->has('brands.total')
            ->has('factories')
            ->has('filters')
            ->has('summary', fn (Assert $summary) => $summary
                ->has('total')
                ->has('active')
                ->has('inactive')
            )
        );
});

test('admin can create a new brand', function (): void {
    $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    $factory = Factory::factory()->create();

    $payload = [
        'name' => 'Akij Ceramics',
        'name_bn' => 'আকিজ সিরামিকস',
        'factory_id' => $factory->id,
        'is_active' => true,
    ];

    $this->actingAs($admin)
        ->post(route('brands.store'), $payload)
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('brands', [
        'name' => 'Akij Ceramics',
        'name_bn' => 'আকিজ সিরামিকস',
        'factory_id' => $factory->id,
    ]);
});

test('non-admin without permission cannot create a brand', function (): void {
    $salesRole = Role::firstOrCreate(['slug' => 'sales_shop'], ['name' => 'Sales shop']);
    $user = User::factory()->create(['role_id' => $salesRole->id]);

    $this->actingAs($user)
        ->post(route('brands.store'), [
            'name' => 'Unauthorized Brand',
        ])
        ->assertForbidden();
});

test('search query filters brands by name or Bengali name', function (): void {
    $user = User::factory()->create();

    Brand::create(['name' => 'Mir Ceramic', 'name_bn' => 'মীর সিরামিক', 'is_active' => true]);
    Brand::create(['name' => 'Star Tiles', 'name_bn' => 'স্টার টাইলস', 'is_active' => true]);

    // Search by English
    $this->actingAs($user)
        ->get(route('brands.index', ['q' => 'Mir']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Brands/Index')
            ->where('brands.total', 1)
            ->where('brands.data.0.name', 'Mir Ceramic')
        );

    // Search by Bengali
    $this->actingAs($user)
        ->get(route('brands.index', ['q' => 'স্টার']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Brands/Index')
            ->where('brands.total', 1)
            ->where('brands.data.0.name', 'Star Tiles')
        );
});

test('filtering by factory and status works properly', function (): void {
    $user = User::factory()->create();

    $factoryA = Factory::factory()->create();
    $factoryB = Factory::factory()->create();

    Brand::create(['name' => 'Active Factory A', 'factory_id' => $factoryA->id, 'is_active' => true]);
    Brand::create(['name' => 'Inactive Factory A', 'factory_id' => $factoryA->id, 'is_active' => false]);
    Brand::create(['name' => 'Active Factory B', 'factory_id' => $factoryB->id, 'is_active' => true]);

    // Filter by factory and status active
    $this->actingAs($user)
        ->get(route('brands.index', [
            'factory_id' => $factoryA->id,
            'status' => 'active',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Brands/Index')
            ->where('brands.total', 1)
            ->where('brands.data.0.name', 'Active Factory A')
        );
});

test('admin can update an existing brand', function (): void {
    $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    $brand = Brand::create(['name' => 'Initial Brand', 'is_active' => true]);

    $this->actingAs($admin)
        ->put(route('brands.update', $brand), [
            'name' => 'Renamed Brand',
            'name_bn' => 'রিনেমড ব্র্যান্ড',
            'is_active' => true,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('brands', [
        'id' => $brand->id,
        'name' => 'Renamed Brand',
    ]);
});

test('admin deleting a brand with products marks it inactive', function (): void {
    $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    $brand = Brand::create(['name' => 'Brand With Product', 'is_active' => true]);
    Product::factory()->create(['brand_id' => $brand->id]);

    $this->actingAs($admin)
        ->delete(route('brands.destroy', $brand))
        ->assertRedirect();

    $this->assertDatabaseHas('brands', [
        'id' => $brand->id,
        'is_active' => false,
    ]);
});

test('admin deleting a brand without products soft deletes it', function (): void {
    $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    $brand = Brand::create(['name' => 'Unused Brand', 'is_active' => true]);

    $this->actingAs($admin)
        ->delete(route('brands.destroy', $brand))
        ->assertRedirect();

    $this->assertSoftDeleted('brands', [
        'id' => $brand->id,
    ]);
});
