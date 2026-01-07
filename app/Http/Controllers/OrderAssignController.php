<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\InventoryItem;
use Illuminate\Support\Facades\DB;

class OrderAssignController extends Controller
{
    public function show(Order $order)
    {
        // Ambil inventory yang masih reserved untuk order ini
        $inventories = InventoryItem::with(['product','branch'])
            ->where('status', 'reserved')
            ->where('reserved_order_id', $order->id)
            ->get();

        return view('manajer_operasional.orders.assign-imei', compact('order', 'inventories'));
    }

    public function assign(Request $request, OrderItem $orderItem)
    {
        $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id'
        ]);

        DB::transaction(function () use ($request, $orderItem) {

            $inventory = InventoryItem::lockForUpdate()
                ->where('id', $request->inventory_item_id)
                ->where('status', 'reserved')
                ->firstOrFail();

            // Assign ke order item
            $orderItem->update([
                'inventory_item_id' => $inventory->id,
                'branch_id' => $inventory->branch_id
            ]);

            // Update inventory
            $inventory->update([
                'status' => 'assigned'
            ]);
        });

        return back()->with('success', 'IMEI berhasil di-assign');
    }
}
