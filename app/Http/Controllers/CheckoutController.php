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
        if (!auth()->guard('customer')->check()) {
            return redirect()->route('ecom.login')
                ->with('error', 'Silakan login terlebih dahulu untuk checkout.');
        }

        $cart = session()->get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('cart.index')
                ->with('error', 'Keranjang belanja kosong.');
        }

        DB::beginTransaction();
        
        try {
            $customerId = auth()->guard('customer')->id();
            $total = 0;
            $branchAllocations = []; // Untuk tracking alokasi per cabang
            
            // Step 1: Validasi dan alokasi stok
            foreach ($cart as $productId => $item) {
                // Cek stok tersedia di seluruh cabang
                $availableItems = InventoryItem::with('branch')
                    ->where('product_id', $productId)
                    ->where('status', 'in_stock')
                    ->where('is_listed', true)
                    ->orderBy('branch_id')
                    ->get();
                
                if ($availableItems->count() < $item['qty']) {
                    $product = Product::find($productId);
                    throw new \Exception(
                        "Stok {$product->name} tidak mencukupi. " .
                        "Tersedia: {$availableItems->count()}, Diminta: {$item['qty']}"
                    );
                }
                
                // Alokasi stok berdasarkan strategi:
                // 1. Prioritaskan cabang dengan stok terbanyak
                // 2. Atau cabang tertentu jika dipilih customer
                
                $allocatedItems = [];
                $remainingQty = $item['qty'];
                
                foreach ($availableItems as $inventoryItem) {
                    if ($remainingQty <= 0) break;
                    
                    $allocatedItems[] = $inventoryItem;
                    $remainingQty--;
                    
                    // Track alokasi per cabang
                    $branchId = $inventoryItem->branch_id;
                    if (!isset($branchAllocations[$branchId])) {
                        $branchAllocations[$branchId] = [];
                    }
                    $branchAllocations[$branchId][] = [
                        'inventory_item' => $inventoryItem,
                        'product_id' => $productId,
                        'qty' => 1,
                        'price' => $item['price']
                    ];
                }
                
                // Simpan alokasi sementara
                $item['allocated_items'] = $allocatedItems;
                $cart[$productId] = $item;
                $total += $item['price'] * $item['qty'];
            }
            
            // Step 2: Buat Order
            $order = Order::create([
                'customer_id' => $customerId,
                'order_number' => 'ORD-' . time() . '-' . rand(1000, 9999),
                'total_amount' => $total,
                'status' => 'pending',
                'payment_status' => 'pending',
                'notes' => $request->input('notes', ''),
                'order_date' => now(),
            ]);
            
            // Step 3: Buat Order Items & Update Inventory
            foreach ($cart as $productId => $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'quantity' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['qty'],
                ]);

            }
            InventoryItem::where('product_id', $productId)
                ->where('status', 'in_stock')
                ->where('is_listed', true)
                ->limit($item['qty'])
                ->update([
                    'status' => 'reserved',
                    'reserved_order_id' => $order->id
                ]);

            // Step 4: Kosongkan cart
            session()->forget('cart');
            
            DB::commit();
            
            // Log activity
            Log::channel('order')->info('Order created', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'customer_id' => $customerId,
                'total' => $total,
                'branch_allocations' => array_keys($branchAllocations)
            ]);
            
            return redirect()->route('orders.show', $order->id)
                ->with('success', 'Pesanan berhasil dibuat!')
                ->with('branch_info', 'Stok dialokasikan dari beberapa cabang.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Checkout error:', [
                'customer_id' => auth()->guard('customer')->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('cart.index')
                ->with('error', 'Terjadi kesalahan saat checkout: ' . $e->getMessage());
        }
    }
    public function confirmStockPickup(Request $request, $orderId)
    {
        // Hanya manager_operasional atau admin yang bisa akses
        if (!auth()->user()->hasRole(['admin', 'manager_operasional'])) {
            abort(403, 'Unauthorized access.');
        }
        
        $order = Order::with(['items.inventoryItem.branch', 'customer'])
            ->findOrFail($orderId);
        
        if ($order->status != 'pending') {
            return back()->with('error', 'Order sudah diproses.');
        }
        
        DB::beginTransaction();
        try {
            // Update status inventory items menjadi "sold"
            foreach ($order->items as $orderItem) {
                if ($orderItem->inventoryItem) {
                    $orderItem->inventoryItem->update([
                        'status' => 'sold',
                        'sold_at' => now()
                    ]);
                }
            }
            
            // Update order status
            $order->update([
                'status' => 'processing',
                'stock_picked_at' => now(),
                'stock_picked_by' => auth()->id()
            ]);
            
            DB::commit();
            
            return redirect()->route('admin.orders.show', $order->id)
                ->with('success', 'Stok berhasil dikonfirmasi diambil dari cabang.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengkonfirmasi pengambilan stok.');
        }
    }
    
    /**
     * Get available branches for a product
     */
    public function getProductBranches($productId)
    {
        $branches = InventoryItem::where('product_id', $productId)
            ->where('status', 'in_stock')
            ->where('is_listed', true)
            ->with('branch')
            ->get()
            ->groupBy('branch_id')
            ->map(function ($items, $branchId) {
                $branch = $items->first()->branch;
                return [
                    'id' => $branchId,
                    'name' => $branch->name,
                    'stock' => $items->count(),
                    'address' => $branch->address,
                    'city' => $branch->city
                ];
            })
            ->values();
            
        return response()->json($branches);
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