<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Role-tailored dashboard overview.
     * Admin sees complete business queues (including purchases and transfers).
     * Sales shop sees counter queues, receipts, and showroom stock (purchases and costs suppressed).
     */
    public function __invoke(Request $request): Response
    {
        $user = $request->user()?->loadMissing(['role', 'defaultWarehouse']);
        $roleSlug = $user?->role?->slug ?? 'admin';
        $roleName = $user?->role?->name ?? 'Admin';
        $warehouse = $user?->defaultWarehouse?->name ?? 'Showroom';
        $isSalesShop = $roleSlug === 'sales_shop';

        if ($isSalesShop) {
            return Inertia::render('Dashboard', [
                'viewer' => [
                    'role' => $roleName,
                    'role_slug' => 'sales_shop',
                    'warehouse' => $warehouse,
                ],
                'filters' => [
                    'warehouse' => $warehouse,
                    'period' => 'Today',
                    'warehouses' => [$warehouse],
                    'periods' => ['Today', 'Yesterday', 'This week'],
                ],
                'today' => [
                    'sales' => '৳64,200',
                    'collected' => '৳52,000',
                    'due_opened' => '৳12,200',
                    'invoices' => 11,
                    'avg_ticket' => '৳5,836',
                ],
                'attention' => [
                    [
                        'label' => 'Low showroom stock',
                        'count' => 3,
                        'tone' => 'warning',
                        'href' => route('inventory.index', absolute: false),
                        'items' => [
                            ['title' => 'RAK-60-WHT', 'meta' => 'Showroom · A1 · 4.00 BOX remaining'],
                            ['title' => 'Carrara 600', 'meta' => 'Showroom · B2 · 2.00 BOX remaining'],
                            ['title' => 'Nila 300 Matte', 'meta' => 'Showroom · C1 · 3.00 BOX remaining'],
                        ],
                    ],
                    [
                        'label' => 'Ready challans to hand over',
                        'count' => 3,
                        'tone' => 'info',
                        'href' => route('challans.index', absolute: false),
                        'items' => [
                            ['title' => 'CH-021', 'meta' => 'Rahman Ceramics · 12 BOX ready'],
                            ['title' => 'CH-020', 'meta' => 'Nila Tiles · 8 BOX ready'],
                            ['title' => 'CH-019', 'meta' => 'Karim Traders · 15 BOX ready'],
                        ],
                    ],
                    [
                        'label' => 'Draft orders pending confirm',
                        'count' => 2,
                        'tone' => 'warning',
                        'href' => route('sales.index', absolute: false),
                        'items' => [
                            ['title' => 'SO-Draft-04', 'meta' => 'Walk-in cash · 5 BOX RAK-60'],
                            ['title' => 'SO-Draft-03', 'meta' => 'Farhana Ent. · 20 BOX Carrara'],
                        ],
                    ],
                ],
                'recentSales' => [
                    ['number' => 'INV-104', 'party' => 'Rahman Ceramics', 'amount' => '৳18,400', 'due' => '৳6,000', 'href' => route('sales.index', absolute: false)],
                    ['number' => 'INV-103', 'party' => 'Walk-in cash', 'amount' => '৳4,280', 'due' => '৳0', 'href' => route('sales.index', absolute: false)],
                    ['number' => 'INV-102', 'party' => 'Nila Tiles', 'amount' => '৳32,150', 'due' => '৳12,150', 'href' => route('sales.index', absolute: false)],
                    ['number' => 'INV-101', 'party' => 'Karim Traders', 'amount' => '৳9,760', 'due' => '৳0', 'href' => route('sales.index', absolute: false)],
                    ['number' => 'INV-100', 'party' => 'Showroom cash', 'amount' => '৳2,150', 'due' => '৳0', 'href' => route('sales.index', absolute: false)],
                    ['number' => 'INV-098', 'party' => 'Walk-in bKash', 'amount' => '৳7,890', 'due' => '৳0', 'href' => route('sales.index', absolute: false)],
                    ['number' => 'INV-097', 'party' => 'Rahman Ceramics', 'amount' => '৳11,200', 'due' => '৳0', 'href' => route('sales.index', absolute: false)],
                    ['number' => 'INV-095', 'party' => 'City Flooring', 'amount' => '৳6,500', 'due' => '৳0', 'href' => route('sales.index', absolute: false)],
                ],
                'recentCollections' => [
                    ['number' => 'RCT-082', 'party' => 'Rahman Ceramics', 'amount' => '৳12,400', 'status' => 'Cash', 'href' => route('payments.index', absolute: false)],
                    ['number' => 'RCT-081', 'party' => 'Walk-in cash', 'amount' => '৳4,280', 'status' => 'Cash', 'href' => route('payments.index', absolute: false)],
                    ['number' => 'RCT-080', 'party' => 'Nila Tiles', 'amount' => '৳20,000', 'status' => 'bKash', 'href' => route('payments.index', absolute: false)],
                    ['number' => 'RCT-079', 'party' => 'Karim Traders', 'amount' => '৳9,760', 'status' => 'Bank', 'href' => route('payments.index', absolute: false)],
                    ['number' => 'RCT-078', 'party' => 'Showroom cash', 'amount' => '৳2,150', 'status' => 'Cash', 'href' => route('payments.index', absolute: false)],
                    ['number' => 'RCT-077', 'party' => 'Walk-in bKash', 'amount' => '৳7,890', 'status' => 'bKash', 'href' => route('payments.index', absolute: false)],
                    ['number' => 'RCT-076', 'party' => 'Rahman Ceramics', 'amount' => '৳11,200', 'status' => 'Cash', 'href' => route('payments.index', absolute: false)],
                    ['number' => 'RCT-075', 'party' => 'City Flooring', 'amount' => '৳6,500', 'status' => 'Nagad', 'href' => route('payments.index', absolute: false)],
                ],
                'topProducts' => [
                    ['name' => 'RAK-60-WHT (600x600)', 'sqft' => '645.60', 'amount' => '৳25,680'],
                    ['name' => 'Carrara 600 Gloss', 'sqft' => '430.40', 'amount' => '৳18,940'],
                    ['name' => 'Nila 300 Matte', 'sqft' => '322.80', 'amount' => '৳11,400'],
                    ['name' => 'Oak Wood 200x1200', 'sqft' => '215.20', 'amount' => '৳12,700'],
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

        // Owner / Admin overview
        return Inertia::render('Dashboard', [
            'viewer' => [
                'role' => $roleName,
                'role_slug' => 'admin',
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
                'avg_ticket' => '৳6,916',
            ],
            'attention' => [
                [
                    'label' => 'Low stock across warehouses',
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
