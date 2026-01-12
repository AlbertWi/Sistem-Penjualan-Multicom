<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchStockController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $userBranchId = $user->branch_id;
        $query = $request->input('q');
        $selectedBranchId = $request->branch_id;
        
        // JANGAN SET DEFAULT JIKA ADA PARAMETER branch_id=null (artinya pilih Semua Cabang)
        // Hanya set default jika benar-benar tidak ada parameter branch_id sama sekali
        // Request dengan ?branch_id= (kosong) artinya user pilih "Semua Cabang"
        
        // Ambil semua cabang
        $branches = Branch::with(['inventoryItems' => function ($q) {
            $q->where('status', 'in_stock')->with('product');
        }])->get();
        
        return view('stok_cabang.index', compact('branches', 'query', 'selectedBranchId', 'userBranchId'));
    }
}