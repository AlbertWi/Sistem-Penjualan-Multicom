<?php

namespace App\Http\Controllers;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        return view('cart.index', compact('cart'));
    }

    public function add(Request $request, Product $product)
    {
        $request->validate([
            'qty' => 'required|integer|min:1'
        ]);

        // pastikan produk diposting
        if (! optional($product->ecomSetting)->is_listed) {
            return back()->with('error', 'Produk tidak tersedia.');
        }

        // cek stok
        $stock = $product->inventoryItems()
            ->where('status', 'in_stock')
            ->count();

        if ($request->qty > $stock) {
            return back()->with('error', 'Stok tidak mencukupi.');
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            $cart[$product->id]['qty'] += $request->qty;
        } else {
            $cart[$product->id] = [
                'product_id' => $product->id,
                'name'       => $product->name,
                'price'      => $product->ecomSetting->ecom_price,
                'qty'        => $request->qty,
                'image'      => optional($product->images->first())->file_path
            ];
        }

        session()->put('cart', $cart);

        return redirect()->route('cart.index')
            ->with('success', 'Produk ditambahkan ke keranjang.');
    }

    public function remove(Product $product)
    {
        $cart = session()->get('cart', []);

        unset($cart[$product->id]);

        session()->put('cart', $cart);

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }
}
