<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the two single-shop roles and the permission catalog.
     */
    public function run(): void
    {
        $permissions = collect($this->catalog())->mapWithKeys(function (array $permission): array {
            $model = Permission::query()->create($permission);

            return [$model->slug => $model->id];
        });

        foreach ($this->roles() as $role) {
            $model = Role::query()->create([
                'name' => $role['name'],
                'slug' => $role['slug'],
            ]);

            $slugs = collect($role['mandatory'])
                ->merge($role['recommended'])
                ->unique()
                ->values();

            $model->permissions()->attach(
                $slugs->map(fn (string $slug): int => $permissions[$slug])->all(),
            );
        }
    }

    /**
     * @return list<array{name: string, slug: string, mandatory: list<string>, recommended: list<string>}>
     */
    private function roles(): array
    {
        return [
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'mandatory' => $this->adminMandatory(),
                'recommended' => $this->adminRecommended(),
            ],
            [
                'name' => 'Sales shop',
                'slug' => 'sales_shop',
                'mandatory' => $this->salesShopMandatory(),
                'recommended' => $this->salesShopRecommended(),
            ],
        ];
    }

    /**
     * Work the shop owner must be able to do alone.
     *
     * @return list<string>
     */
    private function adminMandatory(): array
    {
        return [
            'dashboard.view',
            'products.view',
            'products.create',
            'products.update',
            'products.delete',
            'masterdata.manage',
            'inventory.view',
            'inventory.adjust',
            'warehouses.view',
            'warehouses.manage',
            'transfers.create',
            'transfers.dispatch',
            'transfers.receive',
            'pos.use',
            'sales.view',
            'sales.cancel',
            'purchases.view',
            'purchases.create',
            'purchases.receive',
            'purchases.return',
            'customers.view',
            'customers.manage',
            'customers.ledger',
            'customers.credit.override',
            'suppliers.view',
            'suppliers.manage',
            'suppliers.ledger',
            'payments.customer',
            'payments.supplier',
            'payments.reverse',
            'returns.sales',
            'returns.purchase',
            'challans.view',
            'challans.create',
            'challans.dispatch',
            'reports.sales',
            'reports.stock',
            'reports.finance',
            'users.manage',
            'settings.manage',
        ];
    }

    /**
     * Extra owner controls that a typical showroom should still have.
     *
     * @return list<string>
     */
    private function adminRecommended(): array
    {
        return [
            'sales.discount.unlimited',
            'roles.manage',
            'sms.send',
            'audit.view',
        ];
    }

    /**
     * Counter work a sales shop user must do every day.
     *
     * @return list<string>
     */
    private function salesShopMandatory(): array
    {
        return [
            'dashboard.view',
            'pos.use',
            'sales.view',
            'products.view',
            'inventory.view',
            'customers.view',
            'customers.manage',
            'payments.customer',
        ];
    }

    /**
     * Counter extras a typical showroom should enable for sales shop.
     *
     * @return list<string>
     */
    private function salesShopRecommended(): array
    {
        return [
            'customers.ledger',
            'challans.view',
            'challans.create',
            'returns.sales',
            'reports.sales',
        ];
    }

    /**
     * @return list<array{name: string, slug: string, module: string}>
     */
    private function catalog(): array
    {
        return [
            ['name' => 'View dashboard', 'slug' => 'dashboard.view', 'module' => 'dashboard'],
            ['name' => 'View products', 'slug' => 'products.view', 'module' => 'products'],
            ['name' => 'Create products', 'slug' => 'products.create', 'module' => 'products'],
            ['name' => 'Update products', 'slug' => 'products.update', 'module' => 'products'],
            ['name' => 'Delete products', 'slug' => 'products.delete', 'module' => 'products'],
            ['name' => 'Manage master data', 'slug' => 'masterdata.manage', 'module' => 'masterdata'],
            ['name' => 'View inventory', 'slug' => 'inventory.view', 'module' => 'inventory'],
            ['name' => 'Adjust inventory', 'slug' => 'inventory.adjust', 'module' => 'inventory'],
            ['name' => 'View warehouses', 'slug' => 'warehouses.view', 'module' => 'warehouses'],
            ['name' => 'Manage warehouses', 'slug' => 'warehouses.manage', 'module' => 'warehouses'],
            ['name' => 'Create transfers', 'slug' => 'transfers.create', 'module' => 'transfers'],
            ['name' => 'Dispatch transfers', 'slug' => 'transfers.dispatch', 'module' => 'transfers'],
            ['name' => 'Receive transfers', 'slug' => 'transfers.receive', 'module' => 'transfers'],
            ['name' => 'Use POS', 'slug' => 'pos.use', 'module' => 'sales'],
            ['name' => 'View sales', 'slug' => 'sales.view', 'module' => 'sales'],
            ['name' => 'Cancel sales', 'slug' => 'sales.cancel', 'module' => 'sales'],
            ['name' => 'Unlimited sales discount', 'slug' => 'sales.discount.unlimited', 'module' => 'sales'],
            ['name' => 'View purchases', 'slug' => 'purchases.view', 'module' => 'purchases'],
            ['name' => 'Create purchases', 'slug' => 'purchases.create', 'module' => 'purchases'],
            ['name' => 'Receive purchases', 'slug' => 'purchases.receive', 'module' => 'purchases'],
            ['name' => 'Return purchases', 'slug' => 'purchases.return', 'module' => 'purchases'],
            ['name' => 'View customers', 'slug' => 'customers.view', 'module' => 'customers'],
            ['name' => 'Manage customers', 'slug' => 'customers.manage', 'module' => 'customers'],
            ['name' => 'View customer ledger', 'slug' => 'customers.ledger', 'module' => 'customers'],
            ['name' => 'Override customer credit', 'slug' => 'customers.credit.override', 'module' => 'customers'],
            ['name' => 'View suppliers', 'slug' => 'suppliers.view', 'module' => 'suppliers'],
            ['name' => 'Manage suppliers', 'slug' => 'suppliers.manage', 'module' => 'suppliers'],
            ['name' => 'View supplier ledger', 'slug' => 'suppliers.ledger', 'module' => 'suppliers'],
            ['name' => 'Collect customer payments', 'slug' => 'payments.customer', 'module' => 'payments'],
            ['name' => 'Make supplier payments', 'slug' => 'payments.supplier', 'module' => 'payments'],
            ['name' => 'Reverse payments', 'slug' => 'payments.reverse', 'module' => 'payments'],
            ['name' => 'Process sales returns', 'slug' => 'returns.sales', 'module' => 'returns'],
            ['name' => 'Process purchase returns', 'slug' => 'returns.purchase', 'module' => 'returns'],
            ['name' => 'View challans', 'slug' => 'challans.view', 'module' => 'challans'],
            ['name' => 'Create challans', 'slug' => 'challans.create', 'module' => 'challans'],
            ['name' => 'Dispatch challans', 'slug' => 'challans.dispatch', 'module' => 'challans'],
            ['name' => 'View sales reports', 'slug' => 'reports.sales', 'module' => 'reports'],
            ['name' => 'View stock reports', 'slug' => 'reports.stock', 'module' => 'reports'],
            ['name' => 'View finance reports', 'slug' => 'reports.finance', 'module' => 'reports'],
            ['name' => 'Manage users', 'slug' => 'users.manage', 'module' => 'users'],
            ['name' => 'Manage roles', 'slug' => 'roles.manage', 'module' => 'users'],
            ['name' => 'Manage settings', 'slug' => 'settings.manage', 'module' => 'settings'],
            ['name' => 'Send SMS', 'slug' => 'sms.send', 'module' => 'sms'],
            ['name' => 'View audit log', 'slug' => 'audit.view', 'module' => 'audit'],
        ];
    }
}
