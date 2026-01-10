<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\InventoryItem;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Gunakan guard web (biasa) atau sanctum sesuai konfigurasi
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ]);
    }


    public function logout(Request $request)
    {
        $accessToken = $request->user()->currentAccessToken();
        if (method_exists($accessToken, 'delete')) {
            $accessToken->delete();
        }
        Auth::guard('web')->logout();

        return redirect()->route('login.admin');
    }
        public function showLoginForm()
    {
        return view('auth.login');
    }
    public function dashboard()
    {
        $user = Auth::user();
        switch ($user->role) {
            case 'manajer_operasional':
                return view('dashboard.manajer_operasional', [
                    'totalProducts' => \App\Models\Product::count(),
                    'totalSuppliers' => \App\Models\Supplier::count(),
                    'totalPurchases' => \App\Models\Purchase::count(),
                    'totalTransfers' => \App\Models\StockTransfer::count(),
                ]);

            case 'kepala_toko':
                $branchId = $user->branch_id;
                return view('dashboard.kepala_toko', [
                    'productCount' => \App\Models\Product::count(),
                    'purchaseCount' => \App\Models\Purchase::count(),
                    'supplierCount' => \App\Models\Supplier::count(),
                    'transferCount' => \App\Models\StockTransfer::count(),
                    'totalPurchases' => \App\Models\Purchase::where('branch_id', $branchId)->count(),
                    'totalTransfersIn' => \App\Models\StockTransfer::where('to_branch_id', $branchId)->count(),
                    'totalTransfersOut' => \App\Models\StockTransfer::where('from_branch_id', $branchId)->count(),
                ]);

                case 'owner':
                    $lowStocks = \App\Models\Branch::with(['inventoryItems' => function ($q) {
                        $q->where('status', 'in_stock')->with('product');
                    }])->get()->map(function ($branch) {
                        return [
                            'branch' => $branch->name,
                            'low_stocks' => $branch->inventoryItems
                                ->groupBy('product_id')
                                ->map(function ($items) {
                                    return [
                                        'product' => $items->first()->product->name ?? 'Tidak diketahui',
                                        'qty' => $items->count()
                                    ];
                                })->filter(fn ($item) => $item['qty'] < 2)->values()
                        ];
                    })->filter(fn ($branch) => $branch['low_stocks']->isNotEmpty());
                    /* ================= FILTER GRAFIK ================= */
                    $filter = $request->filter ?? 'bulan';
                    $year   = $request->year ?? now()->year;
                    $month  = $request->month ?? now()->month;
                    $date   = $request->date ?? now()->toDateString();

                    $salesChartQuery = Sale::join('branches', 'branches.id', '=', 'sales.branch_id')
                        ->select(
                            'branches.name as branch',
                            DB::raw('SUM(sales.total) as total')
                        )
                        ->groupBy('branches.name');

                    if ($filter === 'tahun') {
                        $salesChartQuery->whereYear('sales.created_at', $year);
                    }

                    if ($filter === 'bulan') {
                        $salesChartQuery
                            ->whereYear('sales.created_at', $year)
                            ->whereMonth('sales.created_at', $month);
                    }

                    if ($filter === 'hari') {
                        $salesChartQuery->whereDate('sales.created_at', $date);
                    }

                    $salesChart = $salesChartQuery->get();
                    return view('dashboard.owner', [
                        'totalStock' => \App\Models\InventoryItem::where('status', 'in_stock')->count(),
                        'totalBranches' => \App\Models\Branch::count(),
                        'totalAdmins' => \App\Models\User::whereIn('role', ['manajer_operasional', 'kepala_toko'])->count(),
                        'lowStocks' => $lowStocks,
                        'salesChart' => $salesChart,
                        'filter'     => $filter,
                        'year'       => $year,
                        'month'      => $month,
                        'date'       => $date,
                    ]);

            default:
                return redirect()->route('login.admin');
        }
    }
}
