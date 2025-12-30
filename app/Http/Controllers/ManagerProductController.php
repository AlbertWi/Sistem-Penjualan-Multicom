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

        $products = Product::with(['brand', 'ecomSetting'])
            ->whereHas('inventoryItems', function ($q2) {
                $q2->where('status', 'in_stock');
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
                'inventoryItems as stock_count' => function ($q) {
                    $q->where('status', 'in_stock');
                }
            ])
            ->latest()
            ->paginate(30)
            ->appends(['q' => $q]);

        return view('manajer_operasional.inventory.for_ecom', compact('products'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'ecom_price' => 'required|numeric|min:0',
            'is_listed'  => 'nullable|boolean',
        ]);

        ProductEcomSetting::updateOrCreate(
            ['product_id' => $product->id],
            [
                'ecom_price' => $request->ecom_price,
                'is_listed'  => $request->boolean('is_listed'),
            ]
        );

        return back()->with('success', 'Setting e-commerce tersimpan.');
    }
}
