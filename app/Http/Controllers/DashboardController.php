<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Owner / admin overview. Figures are design fixtures until sales and stock modules post live data.
     */
    public function __invoke(Request $request): Response
    {
        $user = $request->user()?->loadMissing(['role', 'defaultWarehouse']);
        $warehouse = $user?->defaultWarehouse?->name ?? 'Showroom';

        return Inertia::render('Dashboard', [
            'viewer' => [
                'role' => $user?->role?->name ?? 'Admin',
                'warehouse' => $warehouse,
            ],
            'filters' => [
                'warehouse' => $warehouse,
                'period' => 'Today',
                'warehouses' => ['Showroom', 'Godown', 'All warehouses'],
                'periods' => ['Today', 'Yesterday', 'This week'],
            ],
            'today' => [
                'sales' => '৳1,24,500',
                'collected' => '৳86,000',
                'due_opened' => '৳38,500',
                'purchases' => '৳52,000',
                'invoices' => 18,
            ],
            'attention' => [
                [
                    'label' => 'Low stock',
                    'count' => 7,
                    'tone' => 'warning',
                    'href' => route('inventory.index', absolute: false),
                    'items' => [
                        ['title' => 'RAK-60-WHT', 'meta' => 'Showroom · A1 · 4.00 BOX'],
                        ['title' => 'Carrara 600', 'meta' => 'Showroom · B2 · 2.00 BOX'],
                        ['title' => 'Nila 300 Matte', 'meta' => 'Godown · C3 · 1.00 BOX'],
                    ],
                ],
                [
                    'label' => 'Pending transfer receives',
                    'count' => 2,
                    'tone' => 'warning',
                    'href' => route('transfers.index', absolute: false),
                    'items' => [
                        ['title' => 'TR-011', 'meta' => 'Godown → Showroom'],
                        ['title' => 'TR-010', 'meta' => 'Godown → Showroom'],
                    ],
                ],
                [
                    'label' => 'Open challans to dispatch',
                    'count' => 4,
                    'tone' => 'info',
                    'href' => route('challans.index', absolute: false),
                    'items' => [
                        ['title' => 'CH-021', 'meta' => 'Rahman Ceramics · dispatched'],
                        ['title' => 'CH-020', 'meta' => 'Nila Tiles · draft'],
                        ['title' => 'CH-019', 'meta' => 'Karim Traders · draft'],
                    ],
                ],
            ],
            'recentSales' => [
                ['number' => 'INV-104', 'party' => 'Rahman Ceramics', 'amount' => '৳18,400', 'due' => '৳6,000', 'href' => route('sales.index', absolute: false)],
                ['number' => 'INV-103', 'party' => 'Walk-in', 'amount' => '৳4,280', 'due' => '৳0', 'href' => route('sales.index', absolute: false)],
                ['number' => 'INV-102', 'party' => 'Nila Tiles', 'amount' => '৳32,150', 'due' => '৳12,150', 'href' => route('sales.index', absolute: false)],
                ['number' => 'INV-101', 'party' => 'Karim Traders', 'amount' => '৳9,760', 'due' => '৳0', 'href' => route('sales.index', absolute: false)],
                ['number' => 'INV-100', 'party' => 'Showroom cash', 'amount' => '৳2,150', 'due' => '৳0', 'href' => route('sales.index', absolute: false)],
                ['number' => 'INV-099', 'party' => 'Farhana Enterprise', 'amount' => '৳21,000', 'due' => '৳21,000', 'href' => route('sales.index', absolute: false)],
                ['number' => 'INV-098', 'party' => 'Walk-in', 'amount' => '৳7,890', 'due' => '৳0', 'href' => route('sales.index', absolute: false)],
                ['number' => 'INV-097', 'party' => 'Rahman Ceramics', 'amount' => '৳11,200', 'due' => '৳0', 'href' => route('sales.index', absolute: false)],
            ],
            'recentPurchases' => [
                ['number' => 'PO-041', 'party' => 'RAK Ceramics', 'amount' => '৳86,000', 'status' => 'partial', 'href' => route('purchases.index', absolute: false)],
                ['number' => 'PO-040', 'party' => 'Fresh Tiles BD', 'amount' => '৳24,500', 'status' => 'received', 'href' => route('purchases.index', absolute: false)],
                ['number' => 'GR-019', 'party' => 'Mirpur Factory', 'amount' => '৳12,800', 'status' => 'posted', 'href' => route('purchases.index', absolute: false)],
                ['number' => 'PO-039', 'party' => 'RAK Ceramics', 'amount' => '৳41,000', 'status' => 'ordered', 'href' => route('purchases.index', absolute: false)],
                ['number' => 'PO-038', 'party' => 'Fresh Tiles BD', 'amount' => '৳18,200', 'status' => 'received', 'href' => route('purchases.index', absolute: false)],
                ['number' => 'GR-018', 'party' => 'Mirpur Factory', 'amount' => '৳9,400', 'status' => 'posted', 'href' => route('purchases.index', absolute: false)],
                ['number' => 'PO-037', 'party' => 'RAK Ceramics', 'amount' => '৳33,000', 'status' => 'ordered', 'href' => route('purchases.index', absolute: false)],
                ['number' => 'PO-036', 'party' => 'Fresh Tiles BD', 'amount' => '৳14,750', 'status' => 'partial', 'href' => route('purchases.index', absolute: false)],
            ],
            'topProducts' => [
                ['name' => 'RAK-60-WHT', 'sqft' => '1,076.00', 'amount' => '৳42,800'],
                ['name' => 'Carrara 600', 'sqft' => '645.60', 'amount' => '৳28,400'],
                ['name' => 'Nila 300 Matte', 'sqft' => '430.40', 'amount' => '৳15,200'],
                ['name' => 'Oak Wood 200x1200', 'sqft' => '322.80', 'amount' => '৳19,050'],
            ],
            'highestDue' => [
                ['name' => 'Nila Tiles', 'balance' => '৳86,400'],
                ['name' => 'Farhana Enterprise', 'balance' => '৳61,000'],
                ['name' => 'Karim Traders', 'balance' => '৳24,750'],
                ['name' => 'Rahman Ceramics', 'balance' => '৳18,200'],
                ['name' => 'City Flooring', 'balance' => '৳9,800'],
            ],
        ]);
    }
}
