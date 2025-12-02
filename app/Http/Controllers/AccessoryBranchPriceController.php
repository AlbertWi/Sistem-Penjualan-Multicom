<?php

namespace App\Http\Controllers;

use App\Models\Accessory;
use App\Models\AccessoryBranchPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccessoryBranchPriceController extends Controller
{
    public function index()
    {
        $branchId = Auth::user()->branch_id; // kepala_toko hanya lihat cabangnya
        $prices = AccessoryBranchPrice::with('accessory')
                    ->where('branch_id', $branchId)
                    ->get();

        return view('accessory_prices.index', compact('prices'));
    }

    public function create()
    {
        $accessories = Accessory::all();
        return view('accessory_prices.create', compact('accessories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'accessory_id' => 'required|exists:accessories,id',
            'price' => 'required|numeric|min:0',
        ]);

        $branchId = Auth::user()->branch_id;

        AccessoryBranchPrice::updateOrCreate(
            [
                'accessory_id' => $request->accessory_id,
                'branch_id' => $branchId
            ],
            [
                'price' => $request->price
            ]
        );

        return redirect()->route('accessory-prices.index')->with('success', 'Harga berhasil disimpan');
    }
}
