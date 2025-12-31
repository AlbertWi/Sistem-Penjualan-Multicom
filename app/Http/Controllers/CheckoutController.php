<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        $orders = Order::with(['items.product'])
            ->where('customer_id', auth()->guard('customer')->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('ecom.checkout.index', compact('orders'));
    }
    public function store(Request $request)
    {
        // Cek apakah customer sudah login
        if (!auth()->guard('customer')->check()) {
            return redirect()->route('ecom.login')
                ->with('error', 'Silakan login terlebih dahulu untuk checkout.');
        }

        // Ambil cart dari session
        $cart = session()->get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('cart.index')
                ->with('error', 'Keranjang belanja kosong.');
        }

        // Mulai transaction
        DB::beginTransaction();
        
        try {
            // Hitung total
            $total = 0;
            foreach ($cart as $item) {
                $total += $item['price'] * $item['qty'];
            }

            // Buat order
            $order = Order::create([
                'customer_id' => auth()->guard('customer')->id(),
                'order_number' => 'ORD-' . time() . '-' . rand(1000, 9999),
                'total_amount' => $total,
                'status' => 'pending',
                'payment_status' => 'pending',
                'notes' => $request->input('notes', ''),
                'order_date' => now(),
            ]);

            // Buat order items
            foreach ($cart as $productId => $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'quantity' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['qty'],
                ]);
            }

            // Kosongkan cart
            session()->forget('cart');

            DB::commit();

            return redirect()->route('orders.show', $order->id)
                ->with('success', 'Pesanan berhasil dibuat!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Checkout error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('cart.index')
                ->with('error', 'Terjadi kesalahan saat checkout. Silakan coba lagi.');
        }
    }

    public function show($id)
    {
        $order = Order::with(['items.product', 'customer'])
            ->where('customer_id', auth()->guard('customer')->id())
            ->findOrFail($id);

        return view('ecom.checkout.show', compact('order'));
    }
    public function payment(Request $request, $id)
    {
        $order = Order::where('customer_id', auth()->guard('customer')->id())
            ->findOrFail($id);
        
        // Validasi
        $request->validate([
            'payment_method' => 'required|string|in:transfer,cash,qris,ewallet',
            'payment_notes' => 'nullable|string|max:500',
        ]);
        
        // Update order
        $order->update([
            'payment_status' => 'paid',
            'payment_method' => $request->payment_method,
            'payment_notes' => $request->payment_notes,
            'paid_at' => now(),
        ]);
        
        // Handle file upload jika ada
        if ($request->hasFile('payment_proof')) {
            $path = $request->file('payment_proof')->store('payment-proofs', 'public');
            $order->update(['payment_proof' => $path]);
        }
        
        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Pembayaran berhasil dikonfirmasi.');
    }

    public function cancel($id)
    {
        $order = Order::where('customer_id', auth()->guard('customer')->id())
            ->findOrFail($id);
        
        // Hanya bisa cancel jika status pending
        if ($order->status == 'pending') {
            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => 'Dibatalkan oleh customer',
            ]);
            
            return redirect()->route('orders.index')
                ->with('success', 'Pesanan berhasil dibatalkan.');
        }
        
        return redirect()->route('orders.index')
            ->with('error', 'Pesanan tidak dapat dibatalkan.');
    }
}