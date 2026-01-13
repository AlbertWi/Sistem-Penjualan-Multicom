<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductEcomSetting;
use Illuminate\Http\Request;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

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
        $branchId = Auth::user()->branch_id;
        if (!$branchId) {
            return back()->withErrors([
                'error' => 'Cabang user tidak ditemukan. Silakan login ulang.'
            ]);
        }
        // ambil atau buat setting ecom per produk
        $setting = ProductEcomSetting::firstOrCreate(
            [
                'product_id' => $product->id,
                'branch_id'  => $branchId,
            ],
            [
                'ecom_price' => 0,
                'is_listed'  => false,
            ]
        );
        $wasListed = $setting->is_listed;
        $isListed = $request->has('is_listed') ? (bool) $request->is_listed : $setting->is_listed;

        $setting->update([
            'ecom_price' => $request->ecom_price,
            'is_listed'  => $request->has('is_listed')
                ? (bool) $request->is_listed
                : $setting->is_listed,
        ]);
        if ($wasListed == false && $isListed == true) {
            $message = 'Produk berhasil di-post ke e-commerce';
        } elseif ($wasListed == true && $isListed == false) {
            $message = 'Produk berhasil di-unpost dari e-commerce';
        } else {
            $message = 'Produk e-commerce berhasil diperbarui';
        }
        return back()->with('success', $message);
    }
    
}
