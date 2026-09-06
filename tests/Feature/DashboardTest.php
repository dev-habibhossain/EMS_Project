<?php

use App\Models\Role;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('owner overview renders the kiln dashboard queues', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('viewer.role', 'Owner')
            ->has('today', fn (Assert $today) => $today
                ->has('sales')
                ->has('collected')
                ->has('due_opened')
                ->has('purchases')
                ->has('invoices'))
            ->has('attention')
            ->has('recentSales')
            ->has('recentPurchases')
            ->has('topProducts')
            ->has('highestDue'));
});

test('overview shows the assigned role name from the database', function () {
    $role = Role::factory()->create([
        'name' => 'Manager',
        'slug' => 'manager',
    ]);
    $user = User::factory()->create(['role_id' => $role->id]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('viewer.role', 'Manager'));
});
