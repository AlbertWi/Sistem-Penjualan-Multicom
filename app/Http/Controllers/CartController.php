<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    /**
     * Display shopping cart
     */
    public function index()
    {
        if (!auth()->guard('customer')->check()) {
            return redirect()->route('customer.login')
                ->with('error', 'Silakan login terlebih dahulu untuk melihat keranjang.');
        }
        
        $cart = session()->get('cart', []);
        
        // Hitung total
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['qty'];
        }
        
        return view('cart.index', compact('cart', 'total'));
    }

    public function add(Request $request)
    {
        // Cek apakah request AJAX
        $isAjax = $request->ajax() || $request->wantsJson();
        
        if (!auth()->guard('customer')->check()) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silakan login terlebih dahulu.',
                    'redirect' => route('customer.login')
                ], 401);
            }
            return redirect()->route('customer.login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }
        
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'qty' => 'required|integer|min:1'
            ]);
            
            $product = Product::with(['ecomSetting', 'images', 'inventoryItems'])
                ->findOrFail($request->product_id);
            
            // Cek apakah produk listed
            if (!optional($product->ecomSetting)->is_listed) {
                throw new \Exception('Produk tidak tersedia untuk dijual.');
            }
            
            // Hitung stok tersedia
            $stock = $product->inventoryItems()
                ->where('status', 'in_stock')
                ->where('is_listed', true)
                ->count();
            
            // Ambil cart saat ini
            $cart = session()->get('cart', []);
            
            // Hitung jumlah yang sudah ada di cart
            $existingQty = isset($cart[$product->id]) ? $cart[$product->id]['qty'] : 0;
            
            // Total yang diminta = existing + new qty
            $totalRequested = $existingQty + $request->qty;
            
            // Validasi stok: total yang diminta tidak boleh melebihi stok
            if ($totalRequested > $stock) {
                $available = $stock - $existingQty;
                
                if ($available <= 0) {
                    throw new \Exception(
                        "Stok habis. Produk sudah ada di keranjang dengan jumlah maksimal ({$existingQty} item)"
                    );
                } else {
                    throw new \Exception(
                        "Stok tidak mencukupi. Hanya tersedia {$available} item lagi (sudah ada {$existingQty} di keranjang)"
                    );
                }
            }
            
            // Tambahkan atau update item di cart
            if (isset($cart[$product->id])) {
                $cart[$product->id]['qty'] += $request->qty;
            } else {
                $cart[$product->id] = [
                    'product_id' => $product->id,
                    'name'  => $product->name,
                    'price' => $product->ecomSetting->ecom_price,
                    'qty'   => $request->qty,
                    'image' => optional($product->images->first())->file_path ?? 'default.jpg',
                    'max_qty' => $stock // Simpan info stok maksimal
                ];
            }
            
            // Simpan cart ke session
            session()->put('cart', $cart);
            session()->save();
            
            // Hitung total item di cart
            $cartCount = collect($cart)->sum('qty');
            
            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => 'Produk berhasil ditambahkan ke keranjang!',
                    'cart_count' => $cartCount,
                    'product_name' => $product->name,
                    'product_qty' => $request->qty,
                    'stock_info' => [
                        'available' => $stock,
                        'current_in_cart' => $cart[$product->id]['qty']
                    ]
                ]);
            }
            
            return redirect()->route('cart.index')
                ->with('success', 'Produk ditambahkan ke keranjang.');
                
        } catch (\Exception $e) {
            Log::error('Add to cart error:', [
                'user_id' => auth()->guard('customer')->id(),
                'product_id' => $request->product_id,
                'error' => $e->getMessage()
            ]);
            
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
            
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove item from cart
     */
    public function remove($productId)
    {
        if (!auth()->guard('customer')->check()) {
            return redirect()->route('customer.login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }
        
        try {
            $cart = session()->get('cart', []);
            
            if (!isset($cart[$productId])) {
                return redirect()->route('cart.index')
                    ->with('error', 'Produk tidak ditemukan di keranjang.');
            }
            
            $productName = $cart[$productId]['name'];
            
            // Hapus item dari cart
            unset($cart[$productId]);
            
            // Update session
            session()->put('cart', $cart);
            session()->save();
            
            return redirect()->route('cart.index')
                ->with('success', "Produk '{$productName}' dihapus dari keranjang.");
                
        } catch (\Exception $e) {
            Log::error('Remove from cart error:', [
                'user_id' => auth()->guard('customer')->id(),
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('cart.index')
                ->with('error', 'Gagal menghapus produk dari keranjang.');
        }
    }

    /**
     * Update item quantity in cart
     */
    public function update(Request $request, $productId)
    {
        if (!auth()->guard('customer')->check()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silakan login terlebih dahulu.'
                ], 401);
            }
            return redirect()->route('customer.login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }
        
        try {
            $request->validate([
                'qty' => 'required|integer|min:1'
            ]);
            
            $cart = session()->get('cart', []);
            
            if (!isset($cart[$productId])) {
                throw new \Exception('Produk tidak ditemukan di keranjang.');
            }
            
            // Ambil produk untuk validasi stok
            $product = Product::with(['ecomSetting', 'inventoryItems'])
                ->findOrFail($productId);
            
            $stock = $product->inventoryItems()
                ->where('status', 'in_stock')
                ->where('is_listed', true)
                ->count();
            
            // Validasi stok
            if ($request->qty > $stock) {
                throw new \Exception(
                    "Stok tidak mencukupi. Maksimal {$stock} item."
                );
            }
            
            // Update quantity
            $cart[$productId]['qty'] = $request->qty;
            
            // Update max_qty jika ada perubahan stok
            $cart[$productId]['max_qty'] = $stock;
            
            // Simpan ke session
            session()->put('cart', $cart);
            session()->save();
            
            // Hitung ulang total
            $cartCount = collect($cart)->sum('qty');
            $itemSubtotal = $cart[$productId]['price'] * $request->qty;
            $cartTotal = collect($cart)->sum(fn($item) => $item['price'] * $item['qty']);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Jumlah produk berhasil diperbarui.',
                    'cart_count' => $cartCount,
                    'item_subtotal' => $itemSubtotal,
                    'cart_total' => $cartTotal
                ]);
            }
            
            return redirect()->route('cart.index')
                ->with('success', 'Jumlah produk berhasil diperbarui.');
                
        } catch (\Exception $e) {
            Log::error('Update cart error:', [
                'user_id' => auth()->guard('customer')->id(),
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
            
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Clear entire cart
     */
    public function clear()
    {
        if (!auth()->guard('customer')->check()) {
            return redirect()->route('customer.login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }
        
        try {
            session()->forget('cart');
            session()->save();
            
            return redirect()->route('cart.index')
                ->with('success', 'Keranjang berhasil dikosongkan.');
                
        } catch (\Exception $e) {
            Log::error('Clear cart error:', [
                'user_id' => auth()->guard('customer')->id(),
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('cart.index')
                ->with('error', 'Gagal mengosongkan keranjang.');
        }
    }

    /**
     * Get cart count for AJAX requests
     */
    public function getCount()
    {
        $cart = session()->get('cart', []);
        $count = collect($cart)->sum('qty');
        
        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    /**
     * Get cart total for AJAX requests
     */
    public function getTotal()
    {
        $cart = session()->get('cart', []);
        $total = collect($cart)->sum(fn($item) => $item['price'] * $item['qty']);
        $count = collect($cart)->sum('qty');
        
        return response()->json([
            'success' => true,
            'total' => $total,
            'count' => $count
        ]);
    }
}