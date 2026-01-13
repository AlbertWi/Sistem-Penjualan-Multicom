<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductEcomSetting;
use Illuminate\Http\Request;
use App\Models\Branch;

class ManagerProductController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->q;
        $onlineBranchId = Branch::online()->firstOrFail()->id;

        $products = Product::with([
                'brand',
                'ecomSetting' => function ($q) use ($onlineBranchId) {
                    $q->where('branch_id', $onlineBranchId);
                }
            ])
            ->whereHas('inventoryItems', function ($q2) use ($onlineBranchId) {
                $q2->where('branch_id', $onlineBranchId)
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
                'inventoryItems as stock_count' => function ($q) use ($onlineBranchId) {
                    $q->where('branch_id', $onlineBranchId)
                    ->where('status', 'in_stock');
                }
            ])
            ->latest()
            ->paginate(30);

        return view('manajer_operasional.inventory.for_ecom', compact('products'));
    }

    public function update(Request $request, Product $product)
    {
        $onlineBranchId = Branch::online()->firstOrFail()->id;

        $request->validate([
            'ecom_price' => 'required|numeric|min:0',
            'is_listed'  => 'nullable|boolean',
        ]);

        // 🔒 Safety: pastikan stok ada di cabang ONLINE
        $stock = $product->inventoryItems()
            ->where('branch_id', $onlineBranchId)
            ->where('status', 'in_stock')
            ->count();

        if ($stock <= 0) {
            return back()->with('error', 'Stok produk di cabang online kosong.');
        }

        ProductEcomSetting::updateOrCreate(
            [
                'product_id' => $product->id,
                'branch_id'  => $onlineBranchId,
            ],
            [
                'ecom_price' => $request->ecom_price,
                'is_listed'  => $request->boolean('is_listed'),
            ]
        );

        return back()->with('success', 'Setting e-commerce tersimpan.');
    }
    public function toggleListing(Product $product)
    {
        $onlineBranchId = Branch::online()->firstOrFail()->id;

        $setting = ProductEcomSetting::where([
            'product_id' => $product->id,
            'branch_id'  => $onlineBranchId,
        ])->first();

        if (!$setting || !$setting->ecom_price) {
            return back()->with('error', 'Isi harga terlebih dahulu.');
        }

        $setting->update([
            'is_listed' => ! $setting->is_listed
        ]);

        return back()->with('success', 'Status produk diperbarui.');
    }
    public function savePrice(Request $request, Product $product)
    {
        $onlineBranchId = Branch::online()->firstOrFail()->id;

        $request->validate([
            'ecom_price' => 'required|numeric|min:0',
        ]);

        ProductEcomSetting::updateOrCreate(
            [
                'product_id' => $product->id,
                'branch_id'  => $onlineBranchId,
            ],
            [
                'ecom_price' => $request->ecom_price,
            ]
        );

        return back()->with('success', 'Harga e-commerce tersimpan.');
    }

}
