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
                'role' => $user?->role?->name ?? 'Owner',
                'warehouse' => $warehouse,
            ],
            'filters' => [
                'warehouse' => $warehouse,
                'period' => 'Today',
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
                ],
                [
                    'label' => 'Pending transfer receives',
                    'count' => 2,
                    'tone' => 'warning',
                ],
                [
                    'label' => 'Open challans to dispatch',
                    'count' => 4,
                    'tone' => 'info',
                ],
            ],
            'recentSales' => [
                ['number' => 'INV-104', 'party' => 'Rahman Ceramics', 'amount' => '৳18,400', 'due' => '৳6,000'],
                ['number' => 'INV-103', 'party' => 'Walk-in', 'amount' => '৳4,280', 'due' => '৳0'],
                ['number' => 'INV-102', 'party' => 'Nila Tiles', 'amount' => '৳32,150', 'due' => '৳12,150'],
                ['number' => 'INV-101', 'party' => 'Karim Traders', 'amount' => '৳9,760', 'due' => '৳0'],
                ['number' => 'INV-100', 'party' => 'Showroom cash', 'amount' => '৳2,150', 'due' => '৳0'],
                ['number' => 'INV-099', 'party' => 'Farhana Enterprise', 'amount' => '৳21,000', 'due' => '৳21,000'],
                ['number' => 'INV-098', 'party' => 'Walk-in', 'amount' => '৳7,890', 'due' => '৳0'],
                ['number' => 'INV-097', 'party' => 'Rahman Ceramics', 'amount' => '৳11,200', 'due' => '৳0'],
            ],
            'recentPurchases' => [
                ['number' => 'PO-041', 'party' => 'RAK Ceramics', 'amount' => '৳86,000', 'status' => 'partial'],
                ['number' => 'PO-040', 'party' => 'Fresh Tiles BD', 'amount' => '৳24,500', 'status' => 'received'],
                ['number' => 'GR-019', 'party' => 'Mirpur Factory', 'amount' => '৳12,800', 'status' => 'posted'],
                ['number' => 'PO-039', 'party' => 'RAK Ceramics', 'amount' => '৳41,000', 'status' => 'ordered'],
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
