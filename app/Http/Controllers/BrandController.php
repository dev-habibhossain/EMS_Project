<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BrandController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = (int) $request->input('per_page', 15);
        if (! in_array($perPage, [15, 25, 50, 100], true)) {
            $perPage = 15;
        }

        $query = Brand::query()
            ->with(['tileFactory:id,name'])
            ->withCount('products')
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $term = trim((string) $request->input('q'));
                $query->where(function (Builder $sub) use ($term): void {
                    $sub->where('name', 'like', "%{$term}%")
                        ->orWhere('name_bn', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('factory_id'), function (Builder $query) use ($request): void {
                $query->where('factory_id', $request->input('factory_id'));
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

        $brands = $query->paginate($perPage)->withQueryString();

        $factories = Factory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $summary = [
            'total' => Brand::query()->count(),
            'active' => Brand::query()->where('is_active', true)->count(),
            'inactive' => Brand::query()->where('is_active', false)->count(),
        ];

        return Inertia::render('Brands/Index', [
            'brands' => $brands,
            'factories' => $factories,
            'filters' => [
                'q' => (string) $request->input('q', ''),
                'factory_id' => $request->input('factory_id'),
                'status' => (string) $request->input('status', 'all'),
                'per_page' => $perPage,
            ],
            'summary' => $summary,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user->isAdmin() && ! $user->hasPermission('masterdata.manage')) {
            abort(403, 'Unauthorized to create brands.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:brands,name'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'factory_id' => ['nullable', 'exists:factories,id'],
            'is_active' => ['boolean'],
        ]);

        Brand::create([
            'name' => trim($validated['name']),
            'name_bn' => ! empty($validated['name_bn']) ? trim($validated['name_bn']) : null,
            'factory_id' => $validated['factory_id'] ?? null,
            'factory_id' => ! empty($validated['factory_id']) ? (int) $validated['factory_id'] : null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'message' => 'Brand created successfully.',
        ]);
    }

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $user = $request->user();
        if (! $user->isAdmin() && ! $user->hasPermission('masterdata.manage')) {
            abort(403, 'Unauthorized to update brands.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('brands', 'name')->ignore($brand->id)],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'factory_id' => ['nullable', 'exists:factories,id'],
            'is_active' => ['boolean'],
        ]);

        $brand->update([
            'name' => trim($validated['name']),
            'name_bn' => ! empty($validated['name_bn']) ? trim($validated['name_bn']) : null,
            'factory_id' => $validated['factory_id'] ?? null,
            'factory_id' => ! empty($validated['factory_id']) ? (int) $validated['factory_id'] : null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'message' => 'Brand updated successfully.',
        ]);
    }

    public function destroy(Request $request, Brand $brand): RedirectResponse
    {
        $user = $request->user();
        if (! $user->isAdmin() && ! $user->hasPermission('masterdata.manage')) {
            abort(403, 'Unauthorized to delete brands.');
        }

        if ($brand->products()->exists()) {
            $brand->update(['is_active' => false]);

            return redirect()->back()->with('toast', [
                'type' => 'warning',
                'message' => 'Brand has active products linked. It was marked Inactive.',
            ]);
        }

        $brand->delete();

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'message' => 'Brand deleted successfully.',
        ]);
    }
}
