<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Factory;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\QualityGrade;
use App\Models\TileSize;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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
                'tileSize:id,label,length_mm,width_mm,default_sqft_per_piece',
                'prices:id,product_id,unit_code,price',
                'warehouseStocks.warehouse:id,name,code',
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
            ->get(['id', 'label', 'length_mm', 'width_mm', 'default_sqft_per_piece']);

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

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user->isAdmin() && ! $user->hasPermission('products.create')) {
            abort(403, 'Unauthorized to create products.');
        }

        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:255', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:255'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'factory_id' => ['nullable', 'exists:factories,id'],
            'tile_size_id' => ['nullable', 'exists:tile_sizes,id'],
            'pieces_per_box' => ['required', 'integer', 'min:1', 'max:100'],
            'sqft_per_piece' => ['required', 'numeric', 'min:0.0001', 'max:999'],
            'box_price' => ['required', 'numeric', 'min:0', 'max:999999'],
            'barcode' => ['nullable', 'string', 'max:255'],
            'requires_batch' => ['boolean'],
            'requires_shade' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        DB::transaction(function () use ($validated): void {
            $product = Product::create([
                'sku' => trim($validated['sku']),
                'name' => trim($validated['name']),
                'name_bn' => ! empty($validated['name_bn']) ? trim($validated['name_bn']) : null,
                'brand_id' => $validated['brand_id'] ?? null,
                'factory_id' => $validated['factory_id'] ?? null,
                'tile_size_id' => $validated['tile_size_id'] ?? null,
                'brand_id' => ! empty($validated['brand_id']) ? (int) $validated['brand_id'] : null,
                'factory_id' => ! empty($validated['factory_id']) ? (int) $validated['factory_id'] : null,
                'tile_size_id' => ! empty($validated['tile_size_id']) ? (int) $validated['tile_size_id'] : null,
                'pieces_per_box' => (int) $validated['pieces_per_box'],
                'sqft_per_piece' => $validated['sqft_per_piece'],
                'barcode' => ! empty($validated['barcode']) ? trim($validated['barcode']) : null,
                'requires_batch' => $validated['requires_batch'] ?? true,
                'requires_shade' => $validated['requires_shade'] ?? true,
                'default_quality_id' => QualityGrade::query()->value('id'),
                'is_active' => $validated['is_active'] ?? true,
            ]);

            ProductPrice::create([
                'product_id' => $product->id,
                'unit_code' => 'BOX',
                'price' => $validated['box_price'],
            ]);
        });

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'message' => 'Product created successfully.',
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $user = $request->user();
        if (! $user->isAdmin() && ! $user->hasPermission('products.update')) {
            abort(403, 'Unauthorized to update products.');
        }

        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($product->id)],
            'name' => ['required', 'string', 'max:255'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'factory_id' => ['nullable', 'exists:factories,id'],
            'tile_size_id' => ['nullable', 'exists:tile_sizes,id'],
            'pieces_per_box' => ['required', 'integer', 'min:1', 'max:100'],
            'sqft_per_piece' => ['required', 'numeric', 'min:0.0001', 'max:999'],
            'box_price' => ['required', 'numeric', 'min:0', 'max:999999'],
            'barcode' => ['nullable', 'string', 'max:255'],
            'requires_batch' => ['boolean'],
            'requires_shade' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        DB::transaction(function () use ($product, $validated): void {
            $product->update([
                'sku' => trim($validated['sku']),
                'name' => trim($validated['name']),
                'name_bn' => ! empty($validated['name_bn']) ? trim($validated['name_bn']) : null,
                'brand_id' => $validated['brand_id'] ?? null,
                'factory_id' => $validated['factory_id'] ?? null,
                'tile_size_id' => $validated['tile_size_id'] ?? null,
                'brand_id' => ! empty($validated['brand_id']) ? (int) $validated['brand_id'] : null,
                'factory_id' => ! empty($validated['factory_id']) ? (int) $validated['factory_id'] : null,
                'tile_size_id' => ! empty($validated['tile_size_id']) ? (int) $validated['tile_size_id'] : null,
                'pieces_per_box' => (int) $validated['pieces_per_box'],
                'sqft_per_piece' => $validated['sqft_per_piece'],
                'barcode' => ! empty($validated['barcode']) ? trim($validated['barcode']) : null,
                'requires_batch' => $validated['requires_batch'] ?? true,
                'requires_shade' => $validated['requires_shade'] ?? true,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            ProductPrice::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'unit_code' => 'BOX',
                ],
                [
                    'price' => $validated['box_price'],
                ]
            );
        });

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'message' => 'Product updated successfully.',
        ]);
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $user = $request->user();
        if (! $user->isAdmin() && ! $user->hasPermission('products.delete')) {
            abort(403, 'Unauthorized to delete products.');
        }

        if ($product->saleItems()->exists() || $product->warehouseStocks()->where('qty_sqft', '>', 0)->exists()) {
            $product->update(['is_active' => false]);

            return redirect()->back()->with('toast', [
                'type' => 'warning',
                'message' => 'Product has stock or transaction history. It was marked Inactive.',
            ]);
        }

        $product->delete();

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'message' => 'Product deleted successfully.',
        ]);
    }
}
