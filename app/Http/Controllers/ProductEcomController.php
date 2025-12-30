<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductEcomSetting;
use Illuminate\Http\Request;

class ProductEcomController extends Controller
{
    /**
     * Update harga e-commerce & status post/unpost
     */
    public function update(Request $request, Product $product)
    {
        // validasi
        $request->validate([
            'ecom_price' => 'required|numeric|min:0',
            'is_listed'  => 'nullable|boolean',
        ]);
        // ambil atau buat setting ecom per produk
        $setting = ProductEcomSetting::firstOrCreate(
            ['product_id' => $product->id],
            [
                'ecom_price' => 0,
                'is_listed'  => false,
            ]
        );
        // update data  
        $setting->update([
            'ecom_price' => $request->ecom_price,
            'is_listed'  => $request->has('is_listed')
                ? (bool) $request->is_listed
                : $setting->is_listed,
        ]);

        return back()->with('success', 'Produk e-commerce berhasil diperbarui');
    }
}
