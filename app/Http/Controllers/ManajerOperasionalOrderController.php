<?php

namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Branch;
use App\Models\InventoryItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
class  ManajerOperasionalOrderController extends Controller
{
    /**
     * Display a listing of online orders
     */
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'items.product'])
            ->orderBy('created_at', 'desc');
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        
        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }
        
        // Search by order number or customer name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }
        
        $orders = $query->paginate(20);
        
        // Statistics
        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'processing_orders' => Order::where('status', 'processing')->count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'pending_payments' => Order::where('payment_status', 'pending')->count(),
        ];
        
        
        return view('manajer_operasional.orders.index', compact('orders', 'stats', ));
    }
    public function show($id)
    {
        $order = Order::with([
            'customer',
            'branch', // Load order branch
            'items' => function($query) {
                $query->with([
                    'product.brand',
                    'product.images',
                    'inventoryItem.branch', // Load inventory item dengan branch-nya
                    'branch' // Load item branch
                ]);
            }
        ])->findOrFail($id);
        
        // Group items by branch - with safer logic
        $itemsByBranch = $order->items->groupBy(function($item) {
            // Priority: inventory_item branch > order_item branch > 'unknown'
            if ($item->inventoryItem && $item->inventoryItem->branch_id) {
                return $item->inventoryItem->branch_id;
            }
            
            if ($item->branch_id) {
                return $item->branch_id;
            }
            
            return 'unknown';
        });
        
        // Get available inventory for each product (for reallocation)
        $availableInventory = [];
        foreach ($order->items as $item) {
            $productId = $item->product_id;
            if (!isset($availableInventory[$productId])) {
                $availableInventory[$productId] = InventoryItem::where('product_id', $productId)
                    ->where('status', 'in_stock')
                    ->where('is_listed', true)
                    ->with('branch')
                    ->get()
                    ->groupBy('branch_id')
                    ->map(function ($items, $branchId) {
                        return [
                            'branch' => $items->first()->branch,
                            'count' => $items->count(),
                            'items' => $items
                        ];
                    });
            }
        }
        
        return view('manajer_operasional.orders.show', compact('order', 'itemsByBranch', 'availableInventory'));
    }
    
    /**
     * Confirm stock pickup from all branches
     */
    public function confirmStockPickup(Request $request, $orderId)
    {
        $order = Order::with('items.inventoryItem')->findOrFail($orderId);
        
        if ($order->status != 'pending') {
            return back()->with('error', 'Order sudah diproses.');
        }
        
        DB::beginTransaction();
        try {
            // Update semua inventory items menjadi sold
            foreach ($order->items as $item) {
                if ($item->inventoryItem) {
                    $item->inventoryItem->update([
                        'status' => 'sold',
                        'sold_at' => now()
                    ]);
                }
            }
            
            // Update order status
            $order->update([
                'status' => 'processing',
                'stock_picked_at' => now(),
                'stock_picked_by' => auth()->id(),
                'notes' => $request->notes ?? $order->notes
            ]);
            
            DB::commit();
            
            // Log activity
            Log::channel('order')->info('Stock pickup confirmed', [
                'order_id' => $order->id,
                'confirmed_by' => auth()->id(),
                'item_count' => $order->items->count()
            ]);
            
            return back()->with('success', 'Pengambilan stok dikonfirmasi. Order siap diproses.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Confirm stock pickup failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Gagal mengkonfirmasi pengambilan stok.');
        }
    }
    
    /**
     * Confirm stock pickup from specific branch
     */
    public function confirmBranchPickup(Request $request, $orderId, $branchId)
    {
        $order = Order::with(['items.inventoryItem', 'items.branch'])->findOrFail($orderId);
        
        DB::beginTransaction();
        try {
            // Update inventory items from specific branch
            $updatedCount = 0;
            foreach ($order->items as $item) {
                if ($item->branch_id == $branchId && $item->inventoryItem) {
                    $item->inventoryItem->update([
                        'status' => 'sold',
                        'sold_at' => now()
                    ]);
                    $updatedCount++;
                }
            }
            
            // If all items from all branches are picked up, update order status
            $allItemsPicked = true;
            foreach ($order->items as $item) {
                if ($item->inventoryItem && $item->inventoryItem->status != 'sold') {
                    $allItemsPicked = false;
                    break;
                }
            }
            
            if ($allItemsPicked) {
                $order->update([
                    'status' => 'processing',
                    'stock_picked_at' => now(),
                    'stock_picked_by' => auth()->id()
                ]);
            }
            
            DB::commit();
            
            return back()->with('success', "Stok dari cabang berhasil dikonfirmasi diambil ({$updatedCount} item).");
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengkonfirmasi pengambilan stok dari cabang.');
        }
    }
    
    /**
     * Mark order as completed
     */
    public function complete(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);
        
        if (!in_array($order->status, ['processing', 'pending'])) {
            return back()->with('error', 'Order tidak dapat diselesaikan.');
        }
        
        $order->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => auth()->id(),
            'notes' => $request->notes ?? $order->notes
        ]);
        
        Log::channel('order')->info('Order completed', [
            'order_id' => $order->id,
            'completed_by' => auth()->id()
        ]);
        
        return back()->with('success', 'Order berhasil ditandai sebagai selesai.');
    }
    
    /**
     * Cancel order
     */
    public function cancel(Request $request, $orderId)
    {
        $order = Order::with('items.inventoryItem')->findOrFail($orderId);
        
        if (!in_array($order->status, ['pending', 'processing'])) {
            return back()->with('error', 'Order tidak dapat dibatalkan.');
        }
        
        DB::beginTransaction();
        try {
            // Return stock to inventory
            foreach ($order->items as $item) {
                if ($item->inventoryItem) {
                    $item->inventoryItem->update([
                        'status' => 'in_stock',
                        'is_listed' => true,
                        'listed_at' => now()
                    ]);
                }
            }
            
            // Update order status
            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
                'cancellation_reason' => $request->reason ?? 'Dibatalkan oleh manajer operasional'
            ]);
            
            DB::commit();
            
            Log::channel('order')->info('Order cancelled', [
                'order_id' => $order->id,
                'cancelled_by' => auth()->id(),
                'reason' => $request->reason
            ]);
            
            return back()->with('success', 'Order berhasil dibatalkan. Stok dikembalikan.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order cancellation failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Gagal membatalkan order.');
        }
    }
    
    /**
     * Get branch stock for order item reallocation
     */
    public function getBranchStock($orderId, $branchId)
    {
        $order = Order::with('items')->findOrFail($orderId);
        
        // Get all products in this order
        $productIds = $order->items->pluck('product_id')->unique();
        
        // Get available inventory for these products in the specified branch
        $availableStock = InventoryItem::whereIn('product_id', $productIds)
            ->where('branch_id', $branchId)
            ->where('status', 'in_stock')
            ->where('is_listed', true)
            ->with(['product', 'branch'])
            ->get()
            ->groupBy('product_id')
            ->map(function ($items, $productId) {
                $product = $items->first()->product;
                return [
                    'product_id' => $productId,
                    'product_name' => $product->name,
                    'product_brand' => $product->brand->name ?? '-',
                    'available_count' => $items->count(),
                    'items' => $items->take(5)->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'imei' => $item->imei,
                            'sku' => $item->sku
                        ];
                    })
                ];
            });
            
        return response()->json([
            'success' => true,
            'data' => $availableStock
        ]);
    }
    
    /**
     * Reallocate stock for order
     */
    public function reallocateStock(Request $request, $orderId)
    {
        $request->validate([
            'reallocations' => 'required|array',
            'reallocations.*.order_item_id' => 'required|exists:order_items,id',
            'reallocations.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'reallocations.*.branch_id' => 'required|exists:branches,id',
        ]);
        
        $order = Order::with('items')->findOrFail($orderId);
        
        DB::beginTransaction();
        try {
            foreach ($request->reallocations as $reallocation) {
                $orderItem = $order->items->find($reallocation['order_item_id']);
                $newInventoryItem = InventoryItem::find($reallocation['inventory_item_id']);
                
                if ($orderItem && $newInventoryItem) {
                    // Return old inventory item to stock
                    if ($orderItem->inventoryItem) {
                        $orderItem->inventoryItem->update([
                            'status' => 'in_stock',
                            'is_listed' => true
                        ]);
                    }
                    
                    // Update order item with new inventory
                    $orderItem->update([
                        'inventory_item_id' => $newInventoryItem->id,
                        'branch_id' => $newInventoryItem->branch_id
                    ]);
                    
                    // Mark new inventory as reserved
                    $newInventoryItem->update([
                        'status' => 'reserved',
                        'is_listed' => false
                    ]);
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Stok berhasil dialokasikan ulang.'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengalokasikan ulang stok.'
            ], 500);
        }
    }
    
    /**
     * Print invoice
     */
    public function printInvoice($orderId)
    {
        $order = Order::with([
            'customer',
            'items' => function($query) {
                $query->with([
                    'product.brand',
                    'inventoryItem'
                ]);
            }
        ])->findOrFail($orderId);
        
        return view('manajer_operasional.orders.print', compact('order'));
    }
    
    /**
     * Update order status
     */
    public function updateStatus(Request $request, $orderId)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled',
            'notes' => 'nullable|string|max:500'
        ]);
        
        $order = Order::findOrFail($orderId);
        
        $updateData = [
            'status' => $request->status,
            'notes' => $request->notes ?? $order->notes
        ];
        
        // Add timestamps based on status
        switch ($request->status) {
            case 'processing':
                $updateData['processed_at'] = now();
                $updateData['processed_by'] = auth()->id();
                break;
            case 'completed':
                $updateData['completed_at'] = now();
                $updateData['completed_by'] = auth()->id();
                break;
            case 'cancelled':
                $updateData['cancelled_at'] = now();
                $updateData['cancelled_by'] = auth()->id();
                $updateData['cancellation_reason'] = $request->cancellation_reason ?? 'Status diperbarui';
                break;
        }
        
        $order->update($updateData);
        
        return back()->with('success', 'Status order berhasil diperbarui.');
    }
    public function assignImei(Order $order, OrderItem $orderItem)
    {
        $order = Order::with(['customer', 'items'])->findOrFail($order->id);
        $orderItem = $order->items->find($orderItem->id);
        
        if (!$orderItem) {
            return back()->with('error', 'Order item tidak ditemukan.');
        }
        
        // Check if already assigned
        if ($orderItem->inventory_item_id) {
            return back()->with('error', 'IMEI sudah di-assign untuk item ini.');
        }
        
        // Get available inventory for this product
        $availableInventory = InventoryItem::where('product_id', $orderItem->product_id)
            ->where('status', 'in_stock')
            ->where('is_listed', true)
            ->with('branch')
            ->orderBy('branch_id')
            ->orderBy('imei')
            ->get();
        
        // Group by branch for better display
        $inventoryByBranch = $availableInventory->groupBy('branch_id')->map(function ($items, $branchId) {
            return [
                'branch' => $items->first()->branch,
                'items' => $items
            ];
        });
        
        return view('manajer_operasional.orders.assign-imei', compact(
            'order',
            'orderItem',
            'availableInventory',
            'inventoryByBranch'
        ));
    }

    /**
     * Store assigned IMEI for order item
     */
    public function storeAssignedImei(Request $request, Order $order, OrderItem $orderItem)
    {
        $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'notes' => 'nullable|string|max:500'
        ]);
        
        $order = Order::with('items')->findOrFail($order->id);
        $orderItem = $order->items->find($orderItem->id);
        
        if (!$orderItem) {
            return back()->with('error', 'Order item tidak ditemukan.');
        }
        
        // Check if inventory item is still available
        $inventoryItem = InventoryItem::where('id', $request->inventory_item_id)
            ->where('product_id', $orderItem->product_id)
            ->where('status', 'in_stock')
            ->where('is_listed', true)
            ->first();
        
        if (!$inventoryItem) {
            return back()->with('error', 'IMEI tidak tersedia atau sudah di-assign.');
        }
        
        DB::beginTransaction();
        try {
            // Update order item with inventory
            $orderItem->update([
                'inventory_item_id' => $inventoryItem->id,
                'branch_id' => $inventoryItem->branch_id
            ]);
            
            // Update inventory status
            $inventoryItem->update([
                'status' => 'reserved',
                'is_listed' => false,
                'reserved_at' => now()
            ]);
            
            // Add notes if provided
            if ($request->notes) {
                $currentNotes = $order->notes ? $order->notes . "\n" : '';
                $order->update([
                    'notes' => $currentNotes . "[IMEI Assignment] " . $request->notes
                ]);
            }
            
            DB::commit();
            
            Log::channel('order')->info('IMEI assigned to order item', [
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
                'inventory_item_id' => $inventoryItem->id,
                'imei' => $inventoryItem->imei,
                'assigned_by' => auth()->id()
            ]);
            
            return redirect()->route('manajer_operasional.orders.show', $order)
                ->with('success', 'IMEI berhasil di-assign ke order item.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('IMEI assignment failed', [
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Gagal assign IMEI: ' . $e->getMessage());
        }
    }
}