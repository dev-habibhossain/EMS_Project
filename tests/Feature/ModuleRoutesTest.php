<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('authenticated users can open module index pages', function (string $route, string $title) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route($route))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('ModuleIndex')
            ->where('title', $title));
})->with([
    ['brands.index', 'Brands'],
    ['factories.index', 'Factories'],
    ['tile-sizes.index', 'Tile sizes'],
    ['shades.index', 'Shades'],
    ['quality-grades.index', 'Quality grades'],
    ['batches.index', 'Batches'],
    ['inventory.index', 'Inventory'],
    ['inventory.damaged', 'Damaged stock'],
    ['warehouses.index', 'Warehouses'],
    ['transfers.index', 'Transfers'],
    ['sales.index', 'Sales'],
    ['purchases.index', 'Purchases'],
    ['challans.index', 'Challans'],
    ['sales-returns.index', 'Sales returns'],
    ['purchase-returns.index', 'Purchase returns'],
    ['customers.index', 'Customers'],
    ['suppliers.index', 'Suppliers'],
    ['payments.index', 'Payments'],
    ['reports.index', 'Reports'],
    ['users.index', 'Users'],
    ['sms-logs.index', 'SMS log'],
    ['activity-logs.index', 'Activity'],
]);

test('guests cannot open module index pages', function () {
    $this->get(route('products.index'))->assertRedirect(route('login'));
});
