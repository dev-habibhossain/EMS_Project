<?php

use App\Models\Brand;
use App\Models\Factory;
use App\Models\Product;
use App\Models\Role;
use App\Models\TileSize;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests cannot view products page', function (): void {
    $this->get(route('products.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users can view products index page with pagination and data', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('products.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Products/Index')
            ->has('products.data')
            ->has('products.current_page')
            ->has('products.total')
            ->has('brands')
            ->has('tileSizes')
            ->has('factories')
            ->has('filters')
            ->has('summary', fn (Assert $summary) => $summary
                ->has('total')
                ->has('active')
                ->has('inactive')
            )
        );
});

test('admin user has admin role in inertia shared props', function (): void {
    $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    $this->actingAs($admin)
        ->get(route('products.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Products/Index')
            ->where('auth.role.slug', 'admin')
        );
});

test('search query filters products by name, sku, or barcode', function (): void {
    $user = User::factory()->create();

    $brand = Brand::factory()->create();
    $factory = Factory::factory()->create();
    $size = TileSize::factory()->create();

    $matchingProduct = Product::create([
        'sku' => 'MATCH-100-TEST',
        'name' => 'Specific Spanish Slate',
        'name_bn' => 'স্প্যানিশ স্লেট',
        'brand_id' => $brand->id,
        'factory_id' => $factory->id,
        'tile_size_id' => $size->id,
        'pieces_per_box' => 4,
        'sqft_per_piece' => '2.690000',
        'barcode' => '894000998877',
        'requires_batch' => true,
        'requires_shade' => true,
        'is_active' => true,
    ]);

    $otherProduct = Product::create([
        'sku' => 'OTHER-200-DIFF',
        'name' => 'Unrelated Floor Glossy',
        'brand_id' => $brand->id,
        'factory_id' => $factory->id,
        'tile_size_id' => $size->id,
        'pieces_per_box' => 4,
        'sqft_per_piece' => '2.690000',
        'is_active' => true,
    ]);

    // Search by SKU
    $this->actingAs($user)
        ->get(route('products.index', ['q' => 'MATCH-100']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Products/Index')
            ->where('products.total', 1)
            ->where('products.data.0.sku', 'MATCH-100-TEST')
        );

    // Search by Barcode
    $this->actingAs($user)
        ->get(route('products.index', ['q' => '894000998877']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Products/Index')
            ->where('products.total', 1)
            ->where('products.data.0.sku', 'MATCH-100-TEST')
        );

    // Search by Bengali name
    $this->actingAs($user)
        ->get(route('products.index', ['q' => 'স্প্যানিশ']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Products/Index')
            ->where('products.total', 1)
            ->where('products.data.0.name', 'Specific Spanish Slate')
        );
});

test('filtering by brand, size, and status works properly', function (): void {
    $user = User::factory()->create();

    $brandA = Brand::factory()->create(['name' => 'Brand Alpha']);
    $brandB = Brand::factory()->create(['name' => 'Brand Beta']);
    $sizeX = TileSize::factory()->create();

    $activeProduct = Product::create([
        'sku' => 'FLT-ACTIVE-A',
        'name' => 'Active Alpha Product',
        'brand_id' => $brandA->id,
        'tile_size_id' => $sizeX->id,
        'pieces_per_box' => 4,
        'sqft_per_piece' => '2.690000',
        'is_active' => true,
    ]);

    $inactiveProduct = Product::create([
        'sku' => 'FLT-INACTIVE-A',
        'name' => 'Inactive Alpha Product',
        'brand_id' => $brandA->id,
        'tile_size_id' => $sizeX->id,
        'pieces_per_box' => 4,
        'sqft_per_piece' => '2.690000',
        'is_active' => false,
    ]);

    // Filter by Brand and Status Active
    $this->actingAs($user)
        ->get(route('products.index', [
            'brand_id' => $brandA->id,
            'status' => 'active',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Products/Index')
            ->where('products.total', 1)
            ->where('products.data.0.sku', 'FLT-ACTIVE-A')
        );

    // Filter by Brand and Status Inactive
    $this->actingAs($user)
        ->get(route('products.index', [
            'brand_id' => $brandA->id,
            'status' => 'inactive',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Products/Index')
            ->where('products.total', 1)
            ->where('products.data.0.sku', 'FLT-INACTIVE-A')
        );
});

test('per_page parameter adjusts page size', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('products.index', ['per_page' => 25]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Products/Index')
            ->where('products.per_page', 25)
        );
});
