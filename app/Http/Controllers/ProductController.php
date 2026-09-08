<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Factory;
use App\Models\Product;
use App\Models\TileSize;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = (int) $request->input('per_page', 15);
        if (! in_array($perPage, [15, 25, 50, 100], true)) {
            $perPage = 15;
        }

        $query = Product::query()
            ->with([
                'brand:id,name',
                'tileFactory:id,name',
                'tileSize:id,label,length_mm,width_mm',
                'prices:id,product_id,unit_code,price',
            ])
            ->withSum('warehouseStocks as total_sqft', 'qty_sqft')
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $term = trim((string) $request->input('q'));
                $query->where(function (Builder $sub) use ($term): void {
                    $sub->where('name', 'like', "%{$term}%")
                        ->orWhere('name_bn', 'like', "%{$term}%")
                        ->orWhere('sku', 'like', "%{$term}%")
                        ->orWhere('barcode', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('brand_id'), function (Builder $query) use ($request): void {
                $query->where('brand_id', $request->input('brand_id'));
            })
            ->when($request->filled('factory_id'), function (Builder $query) use ($request): void {
                $query->where('factory_id', $request->input('factory_id'));
            })
            ->when($request->filled('tile_size_id'), function (Builder $query) use ($request): void {
                $query->where('tile_size_id', $request->input('tile_size_id'));
            })
            ->when($request->filled('status'), function (Builder $query) use ($request): void {
                $status = (string) $request->input('status');
                if ($status === 'active') {
                    $query->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->orderBy('name');

        $products = $query->paginate($perPage)->withQueryString();

        $brands = Brand::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $tileSizes = TileSize::query()
            ->orderBy('label')
            ->get(['id', 'label', 'length_mm', 'width_mm']);

        $factories = Factory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $summary = [
            'total' => Product::query()->count(),
            'active' => Product::query()->where('is_active', true)->count(),
            'inactive' => Product::query()->where('is_active', false)->count(),
        ];

        return Inertia::render('Products/Index', [
            'products' => $products,
            'brands' => $brands,
            'tileSizes' => $tileSizes,
            'factories' => $factories,
            'filters' => [
                'q' => (string) $request->input('q', ''),
                'brand_id' => $request->input('brand_id'),
                'factory_id' => $request->input('factory_id'),
                'tile_size_id' => $request->input('tile_size_id'),
                'status' => (string) $request->input('status', 'all'),
                'per_page' => $perPage,
            ],
            'summary' => $summary,
        ]);
    }
}
