<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use PDF;
use Illuminate\Support\Facades\DB;

class StockReportController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->get('branch_id');

        $branches = Branch::query()
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('id', $branchId);
            })
            ->with(['inventoryItems' => function ($q) {
                $q->where('status', 'in_stock')
                    ->with(['product.brand', 'product.type', 'purchaseItem']);
            }])->get();

        $allBranches = Branch::all();

        return view('owner.stocks.index', compact('branches', 'allBranches', 'branchId'));
    }

    public function print(Request $request)
    {
        $branchId = $request->get('branch_id');

        $branch = Branch::with(['inventoryItems' => function ($q) {
            $q->where('status', 'in_stock')
                ->with(['product.brand', 'product.type', 'purchaseItem']);
        }])->findOrFail($branchId);

        $pdf = PDF::loadView('owner.stocks.print', compact('branch'))
            ->setPaper([0, 0, 684, 792], 'portrait'); // 9.5 x 11 inch

        return $pdf->stream("laporan-stok-{$branch->name}.pdf");
    }
    public function rekap(Request $request)
    {
        $branchId = $request->branch_id;
    
        // Ambil semua cabang untuk dropdown
        $branches = Branch::all();
    
        $rekap = DB::table('products')
            ->join('inventory_items', 'products.id', '=', 'inventory_items.product_id')
            ->select(
                DB::raw("TRIM(REGEXP_REPLACE(products.name, '(BLACK|WHITE|GOLD|GREEN|VIOLET|FOREST OWL|Shadow Ash|SILVER|TIDES|BLUE|PURPLE|RED|GRAY|BROWN|GREY|ORANGE|TITANIUM)$', '')) as base_name"),
                DB::raw("SUBSTRING_INDEX(products.name, ' ', 1) as brand"),
                DB::raw("COUNT(inventory_items.id) as total_stok")
            )
            ->where('inventory_items.status', 'in_stock');
    
        // Jika ada filter cabang
        if ($branchId) {
            $rekap->where('inventory_items.branch_id', $branchId);
        }
    
        $rekap = $rekap
            ->groupBy('brand', 'base_name')
            ->orderBy('brand', 'asc')
            ->orderBy('base_name', 'asc')
            ->get();
    
        // Group by brand untuk di Blade
        $grouped = $rekap->groupBy('brand');
    
        $totalQty = $rekap->sum('total_stok');
    
        return view('reports.stock_summary', compact('grouped', 'totalQty', 'branches', 'branchId'));
    }
    

}
