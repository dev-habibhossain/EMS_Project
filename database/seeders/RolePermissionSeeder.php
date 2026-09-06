<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class RolePermissionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the six shop roles and the permission catalog from docs/04.
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

            $model->permissions()->attach(
                $this->grantSlugs($role['slug'], $permissions->keys())
                    ->map(fn (string $slug): int => $permissions[$slug])
                    ->all(),
            );
        }
    }

    /**
     * @return list<array{name: string, slug: string}>
     */
    private function roles(): array
    {
        return [
            ['name' => 'Owner', 'slug' => 'owner'],
            ['name' => 'Admin', 'slug' => 'admin'],
            ['name' => 'Manager', 'slug' => 'manager'],
            ['name' => 'Salesperson', 'slug' => 'salesperson'],
            ['name' => 'Warehouse', 'slug' => 'warehouse'],
            ['name' => 'Accountant', 'slug' => 'accountant'],
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

    /**
     * @param  Collection<int, string>  $allSlugs
     * @return Collection<int, string>
     */
    private function grantSlugs(string $roleSlug, Collection $allSlugs): Collection
    {
        return match ($roleSlug) {
            'owner' => $allSlugs->values(),
            'admin' => $allSlugs->reject(fn (string $slug): bool => $slug === 'roles.manage')->values(),
            'manager' => collect([
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
                'sales.discount.unlimited',
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
                'returns.sales',
                'returns.purchase',
                'challans.view',
                'challans.create',
                'challans.dispatch',
                'reports.sales',
                'reports.stock',
                'reports.finance',
                'sms.send',
            ]),
            'salesperson' => collect([
                'dashboard.view',
                'products.view',
                'inventory.view',
                'warehouses.view',
                'pos.use',
                'sales.view',
                'customers.view',
                'customers.manage',
                'customers.ledger',
                'payments.customer',
                'returns.sales',
                'challans.view',
                'challans.create',
                'reports.sales',
            ]),
            'warehouse' => collect([
                'dashboard.view',
                'products.view',
                'inventory.view',
                'inventory.adjust',
                'warehouses.view',
                'transfers.create',
                'transfers.dispatch',
                'transfers.receive',
                'purchases.receive',
                'sales.view',
                'customers.view',
                'suppliers.view',
                'returns.sales',
                'challans.view',
                'challans.dispatch',
                'reports.stock',
            ]),
            'accountant' => collect([
                'dashboard.view',
                'products.view',
                'inventory.view',
                'warehouses.view',
                'sales.view',
                'sales.cancel',
                'purchases.view',
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
                'reports.sales',
                'reports.stock',
                'reports.finance',
                'sms.send',
            ]),
            default => collect(),
        };
    }
}
