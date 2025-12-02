<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Brand;
use App\Models\InventoryItem;
use App\Models\SaleItem;
use App\Models\StockTransferItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class SaleController extends Controller
{
    public function index()
    {
        // Ambil penjualan terbaru di cabang user
        $lastSale = \App\Models\Sale::where('branch_id', auth()->user()->branch_id)
            ->latest()
            ->first();
    
        if ($lastSale) {
            return redirect()->route('sales.show', $lastSale->id);
        }
    
        // Kalau belum ada penjualan
        return view('kepala_toko.sales.empty');
    }



    public function show($id)
    {
        $sale = \App\Models\Sale::with([
            'items.product.brand',
            'branch',
            'accessories.accessory'
        ])->findOrFail($id);
    
        $branchId = auth()->user()->branch_id;
    
        // cari previous (id lebih kecil)
        $previous = \App\Models\Sale::where('branch_id', $branchId)
            ->where('id', '<', $sale->id)
            ->orderBy('id', 'desc')
            ->first();
    
        // cari next (id lebih besar)
        $next = \App\Models\Sale::where('branch_id', $branchId)
            ->where('id', '>', $sale->id)
            ->orderBy('id', 'asc')
            ->first();
    
        return view('kepala_toko.sales.show', compact('sale', 'previous', 'next'));
    }



    public function create()
    {
        $products = Product::all();
        $customers = Customer::all();
        return view('kepala_toko.sales.create', compact('products','customers'));
    }

    public function store(Request $request)
{
    Log::info('Sale Store Request:', $request->all());

    $validated = $request->validate([
        'items' => 'required|array|min:1',
        'items.*.imei' => 'nullable|string|distinct',
        'items.*.accessory_id' => 'nullable|integer',
        'items.*.price' => 'required|numeric|min:0',
        'customer_id' => 'nullable|exists:customers,id',
    ]);

    DB::beginTransaction();

    try {
        $total = 0;
        $validItems = [];
        $priceErrors = [];

        foreach ($validated['items'] as $index => $item) {
            $sellPrice = floatval($item['price']);

            // Handle HP (IMEI)
            if (!empty($item['imei'])) {
                $inventory = \App\Models\InventoryItem::with(['product', 'purchaseItem'])
                    ->where('imei', $item['imei'])
                    ->where('branch_id', auth()->user()->branch_id)
                    ->where('status', 'in_stock')
                    ->first();

                if (!$inventory) {
                    throw new \Exception("IMEI {$item['imei']} tidak ditemukan atau sudah terjual.");
                }

                $productModal = floatval($inventory->purchaseItem->price_beli ?? 0);
                if ($productModal > 0 && $sellPrice < $productModal) {
                    $priceErrors[] = [
                        'item' => $inventory->product->name . " (IMEI: {$item['imei']})",
                        'modal' => $productModal,
                        'price' => $sellPrice
                    ];
                }

                $validItems[] = [
                    'type' => 'phone',
                    'inventory' => $inventory,
                    'price' => $sellPrice,
                    'modal' => $productModal
                ];
            }

            // Handle Accessory
            elseif (!empty($item['accessory_id'])) {
                $accessory = \DB::table('accessory_inventories as ai')
                    ->join('accessories as a', 'a.id', '=', 'ai.accessory_id')
                    ->join('purchase_accessories as pa', 'pa.accessory_id', '=', 'a.id')
                    ->where('ai.branch_id', auth()->user()->branch_id)
                    ->where('a.id', $item['accessory_id'])
                    ->where('ai.qty', '>', 0)
                    ->orderBy('pa.created_at', 'desc')
                    ->select(
                        'a.id',
                        'a.name as accessory_name',
                        'ai.qty',
                        'pa.id as purchase_accessory_id', // ✅ ambil id pembelian terakhir
                        'pa.price as modal'
                    )
                    ->first();

                if (!$accessory) {
                    throw new \Exception("Accessory ID {$item['accessory_id']} tidak ditemukan atau stok kosong.");
                }

                $accessoryModal = floatval($accessory->modal ?? 0);
                if ($accessoryModal > 0 && $sellPrice < $accessoryModal) {
                    $priceErrors[] = [
                        'item' => $accessory->accessory_name,
                        'modal' => $accessoryModal,
                        'price' => $sellPrice
                    ];
                }

                $validItems[] = [
                    'type' => 'accessory',
                    'accessory' => $accessory,
                    'price' => $sellPrice,
                    'modal' => $accessory->modal,
                    'purchase_accessory_id' => $accessory->purchase_accessory_id // ✅ simpan id pembelian
                ];
            }

            $total += $sellPrice;
        }

        // Simpan Sale
        $sale = Sale::create([
            'user_id' => auth()->id(),
            'branch_id' => auth()->user()->branch_id,
            'customer_id' => $request->customer_id,
            'total' => $total,
        ]);

        // Simpan Items
        foreach ($validItems as $item) {
            if ($item['type'] === 'phone') {
                $inventory = $item['inventory'];
                $salePrice = $item['price'];

                \App\Models\SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $inventory->product_id,
                    'imei' => $inventory->imei,
                    'price' => $salePrice,
                ]);

                $inventory->status = 'sold';
                $inventory->save();

                $inventoryRecord = \App\Models\Inventory::where('branch_id', auth()->user()->branch_id)
                    ->where('product_id', $inventory->product_id)
                    ->first();

                if ($inventoryRecord && $inventoryRecord->qty > 0) {
                    $inventoryRecord->qty -= 1;
                    $inventoryRecord->save();
                }
            }
            elseif ($item['type'] === 'accessory') {
                $accessory = $item['accessory'];
                $salePrice = $item['price'];

                \App\Models\SaleAccessory::create([
                    'sale_id' => $sale->id,
                    'accessory_id' => $accessory->id,
                    'purchase_accessory_id' => $item['purchase_accessory_id'], // ✅ simpan purchase_accessory_id
                    'qty' => 1,
                    'price' => $salePrice,
                ]);

                \DB::table('accessory_inventories')
                    ->where('branch_id', auth()->user()->branch_id)
                    ->where('accessory_id', $accessory->id)
                    ->decrement('qty', 1);
            }
        }

        DB::commit();

        $totalModal = collect($validItems)->sum('modal');
        $profit = $total - $totalModal;
        Log::info("Sale completed successfully. Total: {$total}, Modal: {$totalModal}, Profit: {$profit}");

        return redirect()->route('sales.index')->with('success', 'Penjualan berhasil disimpan.');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Sale store failed: ' . $e->getMessage());

        return redirect()->back()
            ->withInput()
            ->with('error', 'Gagal menyimpan penjualan: ' . $e->getMessage());
    }
}




    public function searchByImei(Request $request)
    {
        $imei = $request->query('imei');
        $user = auth()->user();
    
        if (!$imei) {
            return response()->json([
                'success' => false,
                'message' => 'IMEI tidak boleh kosong.'
            ]);
        }
    
        // Query dasar
        $query = \App\Models\InventoryItem::with(['product','purchaseItem'])
            ->where('branch_id', $user->branch_id)
            ->where('status', 'in_stock');
    
        // Kalau panjang input 15 → anggap full IMEI (exact match)
        if (strlen($imei) >= 15) {
            $query->where('imei', $imei);
        } else {
            // Kalau input < 15 → anggap pencarian 5 digit belakang
            $query->where('imei', 'like', '%' . $imei);
        }
    
        $inventory = $query->first();
    
        if ($inventory) {
            return response()->json([
                'success' => true,
                'inventory' => [
                    'imei' => $inventory->imei,
                    'purchase_price'=> $inventory->purchaseItem->price ?? 0,
                    'product' => [
                        'id' => $inventory->product->id,
                        'name' => $inventory->product->name,
                        'brand' => $inventory->product->brand,
                        'model' => $inventory->product->model,
                        'price' => $inventory->product->price,
                        'description' => $inventory->product->description ?? '',
                    ],
                    'purchase_price' => $inventory->purchase_price ?? 0,
                ]
            ]);
        }
    
        return response()->json([
            'success' => false,
            'message' => 'IMEI tidak ditemukan dalam database atau sudah terjual.'
        ]);
    }


    public function searchAccessory(Request $request)
    {
        $keyword = $request->get('q');
        $branchId = auth()->user()->branch_id;
    
        $results = \DB::table('accessory_inventories as ai')
            ->join('accessories as a', 'ai.accessory_id', '=', 'a.id')
            ->leftJoin(\DB::raw('(
                SELECT accessory_id, MAX(id) as latest_id
                FROM purchase_accessories
                GROUP BY accessory_id
            ) as latest_pa'), 'latest_pa.accessory_id', '=', 'a.id')
            ->leftJoin('purchase_accessories as pa', 'pa.id', '=', 'latest_pa.latest_id')
            ->where('ai.branch_id', $branchId)
            ->where('a.name', 'like', "%{$keyword}%")
            ->where('ai.qty', '>', 0)
            ->select(
                'a.id as accessory_id',
                'a.name as accessory_name',
                'ai.qty',
                'pa.id as purchase_accessory_id',
                'pa.price as modal'
            )
            ->get();
    
        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }



    public function laporanPenjualan(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal;
        $tanggalAkhir = $request->tanggal_akhir;
        $branchId = $request->branch_id;
        $customerId = $request->customer_id;
        $brandId = $request->brand_id;
    
        // ambil query dengan eager load yang diperlukan
        $query = Sale::with([
            'items.product.brand',
            'items.inventoryItem',
            'accessories.accessory',
            'accessories.purchaseAccessory',
            'branch',
            'customer'
        ]);
    
        // Filter berdasarkan tanggal (jika keduanya diisi)
        if ($tanggalAwal && $tanggalAkhir) {
            $query->whereBetween('created_at', [
                Carbon::parse($tanggalAwal)->startOfDay(),
                Carbon::parse($tanggalAkhir)->endOfDay(),
            ]);
        } elseif ($tanggalAwal) {
            $query->where('created_at', '>=', Carbon::parse($tanggalAwal)->startOfDay());
        } elseif ($tanggalAkhir) {
            $query->where('created_at', '<=', Carbon::parse($tanggalAkhir)->endOfDay());
        }
    
        // Filter cabang
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
    
        // Filter customer
        if ($customerId) {
            $query->where('customer_id', $customerId);
        }
    
        // Filter brand (cek product brand)
        if ($brandId) {
            $query->whereHas('items.product', function ($q) use ($brandId) {
                $q->where('brand_id', $brandId);
            });
        }
    
        // Ambil semua sales yang memenuhi filter
        $penjualan = $query->orderBy('created_at', 'desc')->get();
    
        // Hitung total pendapatan & total laba (termasuk accessories)
        $totalPendapatan = 0;
        $totalLaba = 0;
    
        foreach ($penjualan as $sale) {
            // Produk HP
            foreach ($sale->items ?? [] as $item) {
                $hargaJual = floatval($item->price ?? 0);
                $hargaBeli = floatval($item->inventoryItem->purchase_price ?? 0);
                $totalPendapatan += $hargaJual;
                $totalLaba += ($hargaJual - $hargaBeli);
            }
    
            // Accessories
            foreach ($sale->accessories ?? [] as $acc) {
                $hargaJual = floatval($acc->price ?? 0);
                // try purchaseAccessory relation; jika tidak ada, fallback ke 0
                $hargaBeli = floatval($acc->purchaseAccessory->price ?? 0);
                $totalPendapatan += $hargaJual;
                $totalLaba += ($hargaJual - $hargaBeli);
            }
        }
    
        // Ambil semua data dropdown untuk filter (branch, customer, brand)
        $branches = Branch::all();
        $customers = Customer::all();
        $brands = Brand::all();
    
        // Group sales by customer_id supaya Blade mudah menampilkan per-customer
        // group key: customer_id (null => 'no_customer' group)
        $penjualanGrouped = $penjualan->groupBy(function ($sale) {
            return $sale->customer_id ?? 'no_customer';
        });
    
        // Kirimkan juga selected filter supaya Blade bisa menandai pilihan
        return view('owner.laporan.index', compact(
            'penjualan',
            'penjualanGrouped',
            'tanggalAwal',
            'tanggalAkhir',
            'branchId',
            'customerId',
            'brandId',
            'totalPendapatan',
            'totalLaba',
            'branches',
            'customers',
            'brands'
        ));
    }

    public function exportPdf(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal;
        $tanggalAkhir = $request->tanggal_akhir;
        $branchId = $request->branch_id;

        $query = Sale::with(['items.product', 'branch', 'items.inventoryItem.purchaseItem']);

        // Filter berdasarkan tanggal
        if ($tanggalAwal && $tanggalAkhir) {
            $query->whereBetween('created_at', [
                Carbon::parse($tanggalAwal)->startOfDay(),
                Carbon::parse($tanggalAkhir)->endOfDay(),
            ]);
        }

        // Filter berdasarkan cabang
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $penjualan = $query->get();

        // Hitung total dan laba
        $totalPendapatan = 0;
        $totalLaba = 0;

        foreach ($penjualan as $sale) {
            foreach ($sale->items as $item) {
                $hargaJual = $item->price;
                $hargaBeli = $item->inventoryItem->purchaseItem->price ?? 0;
                $totalPendapatan += $hargaJual;
                $totalLaba += ($hargaJual - $hargaBeli);
            }
        }

        $data = [
            'penjualan' => $penjualan,
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'totalPendapatan' => $totalPendapatan,
            'totalLaba' => $totalLaba,
            'namaPerusahaan' => 'Multicom Group',
        ];

        $pdf = Pdf::loadView('owner.laporan.pdf', $data);
        
        $filename = 'laporan-penjualan-' . ($tanggalAwal ? $tanggalAwal : 'semua') . '-sampai-' . ($tanggalAkhir ? $tanggalAkhir : 'sekarang') . '.pdf';
        
        return $pdf->download($filename);
    }
    public function searchNota(Request $request)
    {
        $q = $request->input('q');
    
        if (!$q) {
            // kalau kosong, tampilkan semua penjualan di cabang user
            $sales = Sale::where('branch_id', auth()->user()->branch_id)
                ->latest()
                ->get();
        } else {
            // cari berdasarkan ID Nota (id sale)
            $sales = Sale::where('branch_id', auth()->user()->branch_id)
                ->where('id', $q)
                ->get();
        }
    
        return view('kepala_toko.sales.search-nota-result', compact('sales'));
    }
    
    public function findImei(Request $request)
    {
        $imei = $request->imei;
        $results = collect();
    
        // 1. Cari di PEMBELIAN (purchase_items)
        // Ambil dari inventory_items yang terkait dengan purchase_item_id
        $purchaseInventory = InventoryItem::with(['product', 'branch', 'purchaseItem.purchase'])
            ->where('imei', $imei)
            ->whereNotNull('purchase_item_id')
            ->get();
    
        foreach ($purchaseInventory as $inv) {
            if ($inv->purchaseItem && $inv->purchaseItem->purchase) {
                $results->push((object)[
                    'imei'      => $inv->imei,
                    'product'   => $inv->product->name,
                    'source'    => 'pembelian',
                    'branch'    => $inv->branch->name,
                    'ref_id'    => $inv->purchaseItem->purchase->id,
                    'date'      => $inv->purchaseItem->purchase->created_at,
                ]);
            }
        }
    
        // 2. Cari di PENJUALAN (sale_items)
        $sales = SaleItem::with(['sale', 'product', 'sale.branch'])
            ->where('imei', $imei)
            ->get();
    
        foreach ($sales as $s) {
            $results->push((object)[
                'imei'      => $s->imei,
                'product'   => $s->product->name,
                'source'    => 'penjualan',
                'branch'    => $s->sale->branch->name,
                'ref_id'    => $s->sale->id,
                'date'      => $s->sale->created_at,
            ]);
        }
    
        // 3. Cari di TRANSFER STOCK (stock_transfer_items)
        $transferItems = StockTransferItem::with([
                'inventoryItem.product',
                'stockTransfer.fromBranch',
                'stockTransfer.toBranch'
            ])
            ->whereHas('inventoryItem', function ($q) use ($imei) {
                $q->where('imei', $imei);
            })
            ->get();
    
        foreach ($transferItems as $t) {
            $results->push((object)[
                'imei'      => $t->inventoryItem->imei,
                'product'   => $t->inventoryItem->product->name,
                'source'    => 'transfer',
                'branch'    => $t->stockTransfer->fromBranch->name . ' → ' . $t->stockTransfer->toBranch->name,
                'ref_id'    => $t->stockTransfer->id,
                'date'      => $t->stockTransfer->created_at,
            ]);
        }
    
        // Sort berdasarkan tanggal (terbaru di atas)
        $results = $results->sortByDesc('date')->values();
    
        return view('kepala_toko.sales.find-imei-result', compact('results'));
    }

    public function print(Sale $sale)
    {
        return view('kepala_toko.sales.print', compact('sale'));
    }

    public function ownerIndex(Request $request)
    {
        $query = Sale::with(['branch', 'customer', 'saleItems.product', 'saleAccessories.accessory']);
    
        // Filter cabang
        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }
    
        // Filter status
        if ($request->status_filter) {
            if ($request->status_filter == 'blm_lunas') {
                $query->where('status', 'blm lunas');
            } elseif ($request->status_filter == 'lunas') {
                $query->where('status', 'lunas');
            }
        }
    
        // Urutkan dari terbaru ke terlama
        $sales = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $branches = Branch::all();
    
        // Hitung total untuk summary cards
        $queryTotal = Sale::query();
        
        // Terapkan filter yang sama untuk perhitungan total
        if ($request->branch_id) {
            $queryTotal->where('branch_id', $request->branch_id);
        }
        if ($request->status_filter) {
            if ($request->status_filter == 'blm_lunas') {
                $queryTotal->where('status', 'blm lunas');
            } elseif ($request->status_filter == 'lunas') {
                $queryTotal->where('status', 'lunas');
            }
        }
    
        // Hitung total seluruh penjualan sesuai filter
        $totalSeluruh = $queryTotal->sum('total');
        
        // Hitung total belum lunas
        $queryBelumLunas = clone $queryTotal;
        $totalBelumLunas = $queryBelumLunas->where('status', 'blm lunas')->sum('total');
        
        // Hitung total lunas
        $queryLunas = clone $queryTotal;
        $totalLunas = $queryLunas->where('status', 'lunas')->sum('total');
    
        return view('owner.sales.index', compact(
            'sales', 
            'branches', 
            'totalSeluruh', 
            'totalBelumLunas', 
            'totalLunas'
        ));
    }
    
    public function pelunasan(Sale $sale)
    {
        // Validasi jika sudah lunas
        if ($sale->status == 'lunas') {
            return redirect()->back()->with('error', 'Penjualan ini sudah lunas.');
        }
    
        $sale->update(['status' => 'lunas']);
    
        return redirect()->back()->with('success', 'Pelunasan berhasil dilakukan.');
    }
    public function searchCustomer(Request $request)
    {
        $keyword = $request->q;
        $customers = \App\Models\Customer::where('name', 'like', "%$keyword%")
            ->orWhere('phone', 'like', "%$keyword%")
            ->limit(10)
            ->get();
    
        return response()->json([
            'success' => true,
            'data' => $customers
        ]);
    }


}