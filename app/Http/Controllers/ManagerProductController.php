<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductEcomSetting;
use Illuminate\Http\Request;

class ManagerProductController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->q;
        $branchId = auth()->user()->branch_id;

        $products = Product::with([
                'brand',
                'ecomSetting' => function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            ])
            ->whereHas('inventoryItems', function ($q2) use ($branchId) {
                $q2->where('branch_id', $branchId)
                ->where('status', 'in_stock');
            })
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhereHas('brand', function ($b) use ($q) {
                            $b->where('name', 'like', "%{$q}%");
                        });
                });
            })
            ->withCount([
                'inventoryItems as stock_count' => function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->where('status', 'in_stock');
                }
            ])
            ->latest()
            ->paginate(30)
            ->appends(['q' => $q]);

        return view('manajer_operasional.inventory.for_ecom', compact('products'));
    }

    public function update(Request $request, Product $product)
    {
        $branchId = auth()->user()->branch_id;

        $request->validate([
            'ecom_price' => 'required|numeric|min:0',
            'is_listed'  => 'nullable|boolean',
        ]);

        // 🔒 Safety: pastikan stok ada di cabang manajer
        $stock = $product->inventoryItems()
            ->where('branch_id', $branchId)
            ->where('status', 'in_stock')
            ->count();

        if ($stock <= 0) {
            return back()->with('error', 'Stok produk di cabang anda kosong.');
        }

        ProductEcomSetting::updateOrCreate(
            [
                'product_id' => $product->id,
                'branch_id'  => $branchId,
            ],
            [
                'ecom_price' => $request->ecom_price,
                'is_listed'  => $request->boolean('is_listed'),
            ]
        );

        return back()->with('success', 'Setting e-commerce tersimpan.');
    }
}
