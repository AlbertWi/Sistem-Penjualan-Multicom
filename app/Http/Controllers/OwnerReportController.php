<?php
namespace App\Http\Controllers;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OwnerReportController extends Controller
{
    public function salesReport(Request $request)
    {
        $startDate = $request->start_date 
            ? Carbon::parse($request->start_date)->startOfDay() 
            : Carbon::now()->startOfMonth();

        $endDate = $request->end_date 
            ? Carbon::parse($request->end_date)->endOfDay() 
            : Carbon::now()->endOfDay();

        $orders = Order::with(['customer', 'items', 'branch'])
            ->whereBetween('order_date', [$startDate, $endDate])
            ->where('status', 'completed') // hanya order selesai
            ->get();

        // =====================
        // Ringkasan Laporan
        // =====================
        $totalOrders = $orders->count();
        $totalRevenue = $orders->sum('total_amount');
        $totalItemsSold = $orders->sum(function ($order) {
            return $order->items->sum('quantity');
        });

        return view('owner.laporan.ecommerce-sales', compact(
            'orders',
            'totalOrders',
            'totalRevenue',
            'totalItemsSold',
            'startDate',
            'endDate'
        ));
    }
}
