<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\InventoryItem;
use App\Models\ProductEcomSetting;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            return redirect()->route('customer.login')
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
            $branchAllocations = [];
            
            // Step 1: Validasi dan alokasi stok
            foreach ($cart as $productId => $item) {
                $ecomSetting = ProductEcomSetting::where('product_id', $productId)
                    ->where('is_listed', true)
                    ->whereHas('branch', function ($q) {
                        $q->where('branch_type', \App\Models\Branch::TYPE_ONLINE);
                    })
                    ->first();
                if (!$ecomSetting) {
                    $product = Product::find($productId);
                    throw new \Exception("Produk {$product->name} tidak tersedia untuk dijual.");
                }
                $availableItems = InventoryItem::with('branch')
                    ->where('product_id', $productId)
                    ->where('status', 'in_stock')
                    ->whereHas('branch', function ($q) {
                        $q->where('branch_type', \App\Models\Branch::TYPE_ONLINE);
                    })
                    ->whereDoesntHave('orderItems', function($query) {
                        $query->whereHas('order', function($q) {
                            $q->whereIn('status', ['pending', 'processing']);
                        });
                    })
                    ->orderBy('id') // urut IMEI biar konsisten
                    ->limit($item['qty'] * 2)
                    ->get();
                if ($availableItems->count() < $item['qty']) {
                    $product = Product::find($productId);
                    throw new \Exception(
                        "Stok {$product->name} tidak mencukupi. " .
                        "Tersedia: {$availableItems->count()}, Diminta: {$item['qty']}"
                    );
                }
                
                $allocatedItems = [];
                $remainingQty = $item['qty'];
                
                foreach ($availableItems as $inventoryItem) {
                    if ($remainingQty <= 0) break;
                    
                    // Double check: Pastikan item ini belum di-assign
                    $alreadyAssigned = OrderItem::where('inventory_item_id', $inventoryItem->id)
                        ->whereHas('order', function($q) {
                            $q->whereIn('status', ['pending', 'processing']);
                        })
                        ->exists();
                    
                    if ($alreadyAssigned) {
                        Log::warning('Inventory already assigned, skipping', [
                            'inventory_id' => $inventoryItem->id,
                            'imei' => $inventoryItem->imei
                        ]);
                        continue;
                    }
                    
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
                        'price' => $item['price'],
                        'ecom_setting' => $ecomSetting
                    ];
                }
                
                // Validasi final: Apakah cukup setelah filtering?
                if (count($allocatedItems) < $item['qty']) {
                    $product = Product::find($productId);
                    throw new \Exception(
                        "Stok {$product->name} tidak mencukupi setelah validasi. " .
                        "Tersedia: " . count($allocatedItems) . ", Diminta: {$item['qty']}"
                    );
                }
                
                // Simpan alokasi sementara
                $item['allocated_items'] = $allocatedItems;
                $item['ecom_setting'] = $ecomSetting; 
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
            
            // Step 3: Buat Order Items & Reserve Inventory
            foreach ($cart as $productId => $item) {
                foreach ($item['allocated_items'] as $inventoryItem) {
                    // Buat order item dengan inventory_item_id
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $productId,
                        'inventory_item_id' => $inventoryItem->id, // ASSIGN LANGSUNG
                        'branch_id' => $inventoryItem->branch_id,
                        'quantity' => 1, // 1 item = 1 inventory_item
                        'price' => $item['price'],
                        'subtotal' => $item['price'],
                        'ecom_setting_id' => $item['ecom_setting']->id,
                    ]);
                    
                    // Update inventory status ke reserved
                    $inventoryItem->update([
                        'status' => 'reserved',
                        'reserved_at' => now()
                    ]);
                }
            }
            
            // Step 4: Kosongkan cart
            session()->forget('cart');
            
            DB::commit();
            
            return redirect()->route('customer.orders.show', $order->id)
                ->with('success', 'Pesanan berhasil dibuat!')
                ->with('info', 'Silakan lakukan pembayaran untuk melanjutkan pesanan.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('cart.index')
                ->with('error', 'Terjadi kesalahan saat checkout: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $order = Order::with(['items.product.brand', 'items.inventoryItem', 'customer'])
            ->where('customer_id', auth()->guard('customer')->id())
            ->findOrFail($id);

        return view('ecom.checkout.show', compact('order'));
    }

    public function payment(Request $request, $id)
    {
        $order = Order::where('customer_id', auth()->guard('customer')->id())
            ->findOrFail($id);

        // ✅ VALIDASI
        $request->validate([

            'payment_method'  => 'required|string|in:transfer,cash,qris,ewallet',
            'payment_notes'   => 'nullable|string|max:500',
        ]);

        // ✅ UPDATE ORDER + ALAMAT
        $order->update([
            'payment_status'   => 'paid',
            'payment_method'   => $request->payment_method,
            'payment_notes'    => $request->payment_notes,
            'paid_at'          => now(),
        ]);

        // ✅ UPLOAD BUKTI PEMBAYARAN
        if ($request->hasFile('payment_proof')) {
            $path = $request->file('payment_proof')
                ->store('payment-proofs', 'public');

            $order->update(['payment_proof' => $path]);
        }

        Log::channel('order')->info('Payment confirmed', [
            'order_id' => $order->id,
            'payment_method' => $request->payment_method
        ]);

        return redirect()->route('customer.orders.show', $order->id)
            ->with('success', 'Pembayaran berhasil.');
    }

    public function cancel(Request $request, $orderId)
    {
        $request->validate([
            'reason' => 'required|string'
        ]);

        $order = Order::with('items.inventoryItem.product')->findOrFail($orderId);

        if (!in_array($order->status, ['pending', 'processing'])) {
            return back()->with('error', 'Order tidak dapat dibatalkan.');
        }

        DB::beginTransaction();
        try {

            foreach ($order->items as $item) {

                // 1️⃣ Kembalikan stok (INVENTORY)
                if (
                    $item->inventoryItem &&
                    $item->inventoryItem->status === 'reserved'
                ) {
                    $item->inventoryItem->update([
                        'status' => 'in_stock'
                    ]);
                }

                // 2️⃣ Aktifkan kembali listing e-commerce (PRODUCT_ECOM_SETTINGS)
                if ($item->product && $item->product->ecomSetting) {
                    $item->product->ecomSetting->update([
                        'is_listed' => true,
                        'listed_at' => now()
                    ]);
                }
            }

            // 3️⃣ Update order
            $order->update([
                'status'               => 'cancelled',
                'cancelled_at'         => now(),
                'cancelled_by'         => auth()->id(),
                'cancellation_reason'  => $request->reason,
            ]);

            DB::commit();

            return back()->with('success', 'Order berhasil dibatalkan & stok dikembalikan.');

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error('Order cancellation failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal membatalkan order. Cek log.');
        }
    }

    
}