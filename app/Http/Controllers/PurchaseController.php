<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\InventoryItem;
use App\Models\Inventory;
use App\Models\Accessory;
use App\Models\AccessoryBranchPrice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurchaseController extends Controller
{
    public function index()
    {
    $user = auth()->user();
    if ($user->role === 'kepala_toko') {
        $purchases = Purchase::where('branch_id', $user->branch_id)->latest()->get();
    } else {
        $purchases = Purchase::where('branch_id', $user->branch_id)->latest()->get();
    }
    return view('purchases.index', compact('purchases'));
    }
    public function create()
    {
        $suppliers = Supplier::all();
        $products = Product::all();
        $accessories = Accessory::all();
    
        // Ambil harga terakhir untuk tiap produk
        $lastPrices = \App\Models\PurchaseItem::select('product_id', 'price')
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')
                  ->from('purchase_items')
                  ->groupBy('product_id');
            })
            ->pluck('price', 'product_id'); // hasil: [product_id => last_price]
    
        return view('purchases.create', compact('suppliers', 'products', 'accessories', 'lastPrices'));
    }
   public function store(Request $request)
    {
        // Normalisasi harga & qty untuk products
        $normalizedProducts = [];
        foreach ($request->products ?? [] as $i => $item) {
            $normalizedProducts[$i] = $item;
            $normalizedProducts[$i]['price'] = str_replace(',', '', $item['price'] ?? 0);
            $normalizedProducts[$i]['qty'] = str_replace(',', '', $item['qty'] ?? 0);
        }
    
        // Normalisasi harga & qty untuk accessories
        $normalizedAccessories = [];
        foreach ($request->accessories ?? [] as $i => $item) {
            $normalizedAccessories[$i] = $item;
            $normalizedAccessories[$i]['price'] = str_replace(',', '', $item['price'] ?? 0);
            $normalizedAccessories[$i]['qty'] = str_replace(',', '', $item['qty'] ?? 0);
        }
    
        $request->merge([
            'products' => $normalizedProducts,
            'accessories' => $normalizedAccessories
        ]);
    
        // Validasi
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'products' => 'nullable|array',
            'products.*.product_id' => 'required_with:products|exists:products,id',
            'products.*.qty' => 'required_with:products|numeric|min:1',
            'products.*.price' => 'required|required_with:products|numeric|min:1',
    
            'accessories' => 'nullable|array',
            'accessories.*.accessory_id' => 'required_with:accessories|exists:accessories,id',
            'accessories.*.qty' => 'required_with:accessories|numeric|min:1',
            'accessories.*.price' => 'required_with:accessories|numeric|min:1',
        ],[
            'supplier_id.required' => 'Supplier harus dipilih.',
    
            'products.*.product_id.required_with' => 'Produk HP harus dipilih.',
            'products.*.qty.required_with' => 'Qty HP harus diisi.',
            'products.*.price.required_with' => 'Harga HP harus diisi.',
    
            'accessories.*.accessory_id.required_with' => 'Accessories harus dipilih.',
            'accessories.*.qty.required_with' => 'Qty Accessories harus diisi.',
            'accessories.*.price.required_with' => 'Harga Accessories harus diisi.',
        ]);
        if (empty($request->products) && empty($request->accessories)) {
        return redirect()->back()
            ->withErrors(['products' => 'Harus menambahkan minimal satu Produk HP atau Accessories.'])
            ->withInput();
        }
    
        $purchaseDate = Carbon::now();
        $user = Auth::user();
        $branchId = $user->branch_id ?? Branch::first()?->id;
    
        DB::beginTransaction();
        try {
            // Simpan master pembelian
            $purchase = Purchase::create([
                'user_id' => $user->id,
                'branch_id' => $branchId,
                'supplier_id' => $request->supplier_id,
                'purchase_date' => $purchaseDate,
            ]);
    
            /** ================= PRODUK HP ================= */
            foreach ($request->products ?? [] as $item) {
                $purchaseItem = PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                ]);
    
                $purchaseItem->refresh();
    
                $inventory = Inventory::firstOrCreate([
                    'product_id' => $item['product_id'],
                    'branch_id' => $branchId,
                ]);
                $inventory->qty = ($inventory->exists ? $inventory->qty : 0) + $item['qty'];
                $inventory->save();
    
                // Buat inventory items untuk HP (IMEI)
                for ($i = 0; $i < $item['qty']; $i++) {
                    InventoryItem::create([
                        'branch_id' => $branchId,
                        'product_id' => $item['product_id'],
                        'inventory_id' => $inventory->id,
                        'imei' => null,
                        'purchase_item_id' => $purchaseItem->id,
                        'status' => 'in_stock',
                        'purchase_price' => $item['price'],
                    ]);
                }
            }
    
            /** ================= ACCESSORIES ================= */
            foreach ($request->accessories ?? [] as $item) {
                // simpan histori pembelian accessories
                $purchaseAccessory = \App\Models\PurchaseAccessory::create([
                    'purchase_id' => $purchase->id,
                    'accessory_id' => $item['accessory_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                ]);
    
                // update stok per cabang
                $accessoryStock = \App\Models\AccessoryInventory::firstOrCreate([
                    'accessory_id' => $item['accessory_id'],
                    'branch_id' => $branchId,
                ]);
                $accessoryStock->qty += $item['qty'];
                $accessoryStock->save();
            }
    
            DB::commit();
            return redirect()->route('purchases.index')->with('success', 'Pembelian berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Purchase store error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menambahkan pembelian: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $purchase = Purchase::with([
            'supplier',
            'branch',
            'items.product',
            'items.inventoryItems.product',
            'purchaseAccessories.accessory' // ✅ tambahkan relasi accessories
        ])->findOrFail($id);
    
        return view('purchases.show', compact('purchase'));
    }


    public function saveImei(Request $request, Purchase $purchase)
    {
        $imeis = $request->input('imeis', []);
        $inventories = $purchase->items->flatMap(fn ($item) => $item->inventoryItems);
        $errorMessages = [];
    
        foreach ($inventories as $inventory) {
    
            $inputImei = trim($imeis[$inventory->id] ?? '');
    
            if (!$inputImei) continue;
    
            // ========== FIX TERPENTING ==========
            // Jika IMEI sama dengan yang sudah tersimpan → jangan update
            if ($inventory->imei === $inputImei) {
                continue;
            }
    
            $currentProductId = $inventory->product_id;
    
            // Cek apakah IMEI pernah ada di inventory lain
            $existing = InventoryItem::where('imei', $inputImei)
                ->where('id', '!=', $inventory->id)
                ->first();
    
            if ($existing) {
    
                // CASE 1: IMEI masih in_stock (belum pernah dijual)
                if ($existing->status == 'in_stock') {
                    $errorMessages[] = "IMEI $inputImei sudah terdaftar di stok dan tidak boleh digunakan lagi.";
                    continue;
                }
    
                // CASE 2: Sudah SOLD tapi produk berbeda
                if ($existing->product_id != $currentProductId) {
                    $errorMessages[] = "IMEI $inputImei pernah digunakan di produk berbeda dan tidak bisa dipakai ulang.";
                    continue;
                }
    
                // CASE 3: Sudah SOLD dan produk sama → BOLEH
            }
    
            try {
                $inventory->imei = $inputImei;
                $inventory->save();
            } catch (\Exception $e) {
                \Log::error('Gagal simpan IMEI: ' . $e->getMessage());
                $errorMessages[] = "Gagal menyimpan IMEI {$inputImei}: {$e->getMessage()}";
            }
        }
    
        if (!empty($errorMessages)) {
            return redirect()->back()->withErrors($errorMessages)->withInput();
        }
    
        return redirect()->route('purchases.index')->with('success', 'IMEI berhasil disimpan.');
    }



    public function ownerIndex(Request $request)
{
    // Query purchases (eager load items + accessories)
    $query = Purchase::with(['branch', 'supplier', 'items', 'accessories'])
        ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
        ->when($request->status_filter, function($q) use ($request) {
            if ($request->status_filter == 'blm_lunas') {
                $q->where('status', 'blm lunas');
            } elseif ($request->status_filter == 'lunas') {
                $q->where('status', 'lunas');
            }
        })
        ->orderBy('created_at', 'desc');

    $purchases = $query->paginate(20);

    // --- Hitung total semua (produk + accessories) dengan memperhitungkan filter ---
    $itemsQuery = DB::table('purchase_items')
        ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id');

    $accessoriesQuery = DB::table('purchase_accessories')
        ->join('purchases', 'purchase_accessories.purchase_id', '=', 'purchases.id');

    // Terapkan filter branch
    if ($request->branch_id) {
        $itemsQuery->where('purchases.branch_id', $request->branch_id);
        $accessoriesQuery->where('purchases.branch_id', $request->branch_id);
    }

    // Terapkan filter status untuk total semua
    if ($request->status_filter) {
        if ($request->status_filter == 'blm_lunas') {
            $itemsQuery->where('purchases.status', 'blm lunas');
            $accessoriesQuery->where('purchases.status', 'blm lunas');
        } elseif ($request->status_filter == 'lunas') {
            $itemsQuery->where('purchases.status', 'lunas');
            $accessoriesQuery->where('purchases.status', 'lunas');
        }
    }

    $totalItems = (float) $itemsQuery->sum(DB::raw('purchase_items.qty * purchase_items.price'));
    $totalAccessories = (float) $accessoriesQuery->sum(DB::raw('purchase_accessories.qty * purchase_accessories.price'));

    $totalSemua = $totalItems + $totalAccessories;

    // --- Hitung total belum lunas (hanya purchase dengan status 'blm lunas') ---
    $blmQuery = Purchase::where('status', 'blm lunas')
        ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id));

    $blmIds = $blmQuery->pluck('id')->toArray();

    $totalBlmItems = 0;
    $totalBlmAccessories = 0;
    if (!empty($blmIds)) {
        $totalBlmItems = (float) DB::table('purchase_items')
            ->whereIn('purchase_id', $blmIds)
            ->sum(DB::raw('qty * price'));

        $totalBlmAccessories = (float) DB::table('purchase_accessories')
            ->whereIn('purchase_id', $blmIds)
            ->sum(DB::raw('qty * price'));
    }

    $totalBlmLunas = $totalBlmItems + $totalBlmAccessories;

    // --- Untuk tiap purchase page, tambahkan atribut total (produk+accessories) ---
    foreach ($purchases as $purchase) {
        $purchaseTotalItems = $purchase->items->sum(fn($i) => ($i->qty ?? 0) * ($i->price ?? 0));
        $purchaseTotalAccessories = $purchase->accessories->sum(fn($a) => ($a->qty ?? 0) * ($a->price ?? 0));
        $purchase->total = $purchaseTotalItems + $purchaseTotalAccessories;
    }

    $branches = Branch::all();

    return view('owner.purchases.index', compact('purchases', 'branches', 'totalSemua', 'totalBlmLunas'));
}

public function pelunasan(\App\Models\Purchase $purchase)
{
    if ($purchase->status == 'lunas') {
        return redirect()->back()->with('error', 'Pembelian sudah lunas sebelumnya.');
    }

    $purchase->update(['status' => 'lunas']);

    return redirect()->back()->with('success', 'Pelunasan berhasil dilakukan.');
}



}
