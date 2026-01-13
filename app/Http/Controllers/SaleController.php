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
        $tanggalAwal = $request->tanggal_awal ?? now()->subDays(30)->format('Y-m-d');
        $tanggalAkhir = $request->tanggal_akhir ?? now()->format('Y-m-d');
        $branchId = $request->branch_id;
        $brandId = $request->brand_id;

        // Query untuk mendapatkan data penjualan
        $query = Sale::with([
            'items.product.brand',
            'items.inventoryItem',
            'accessories.accessory',
            'accessories.purchaseAccessory',
            'branch',
            'customer'
        ]);

        // Filter tanggal
        if ($tanggalAwal && $tanggalAkhir) {
            $query->whereBetween('created_at', [
                Carbon::parse($tanggalAwal)->startOfDay(),
                Carbon::parse($tanggalAkhir)->endOfDay(),
            ]);
        }

        // Filter cabang
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        // Filter brand
        if ($brandId) {
            $query->whereHas('items.product', function ($q) use ($brandId) {
                $q->where('brand_id', $brandId);
            });
        }

        // Get all sales data
        $sales = $query->orderBy('created_at', 'desc')->get();

        // REKAP PER CABANG
        $rekapCabang = [];
        $totalSemua = [
            'pendapatan' => 0,
            'laba' => 0,
            'jumlah_nota' => 0,
            'jumlah_item' => 0,
            'jumlah_customer' => 0
        ];

        // Kelompokkan data per cabang
        $salesGroupedByBranch = $sales->groupBy('branch_id');

        foreach ($salesGroupedByBranch as $branchId => $salesInBranch) {
            $branch = $salesInBranch->first()->branch ?? null;
            if (!$branch) continue;

            $pendapatanCabang = 0;
            $labaCabang = 0;
            $jumlahNota = $salesInBranch->count();
            $jumlahItem = 0;
            $customers = collect();

            foreach ($salesInBranch as $sale) {
                // Hitung produk HP
                foreach ($sale->items ?? [] as $item) {
                    $hargaJual = floatval($item->price ?? 0);
                    $hargaBeli = floatval($item->inventoryItem->purchase_price ?? 0);
                    $pendapatanCabang += $hargaJual;
                    $labaCabang += ($hargaJual - $hargaBeli);
                    $jumlahItem++;
                }

                // Hitung accessories
                foreach ($sale->accessories ?? [] as $acc) {
                    $hargaJual = floatval($acc->price ?? 0);
                    $hargaBeli = floatval($acc->purchaseAccessory->price ?? 0);
                    $pendapatanCabang += $hargaJual;
                    $labaCabang += ($hargaJual - $hargaBeli);
                    $jumlahItem++;
                }

                // Kumpulkan customer unik
                if ($sale->customer) {
                    $customers->push($sale->customer_id);
                }
            }

            // Hitung margin laba (dalam persen)
            $marginLaba = $pendapatanCabang > 0 ? ($labaCabang / $pendapatanCabang) * 100 : 0;

            $rekapCabang[] = [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'pendapatan' => $pendapatanCabang,
                'laba' => $labaCabang,
                'margin_laba' => $marginLaba,
                'jumlah_nota' => $jumlahNota,
                'jumlah_item' => $jumlahItem,
                'jumlah_customer' => $customers->unique()->count(),
                'avg_transaksi' => $jumlahNota > 0 ? $pendapatanCabang / $jumlahNota : 0,
            ];

            // Akumulasi total semua cabang
            $totalSemua['pendapatan'] += $pendapatanCabang;
            $totalSemua['laba'] += $labaCabang;
            $totalSemua['jumlah_nota'] += $jumlahNota;
            $totalSemua['jumlah_item'] += $jumlahItem;
            $totalSemua['jumlah_customer'] += $customers->unique()->count();
        }

        // Urutkan cabang berdasarkan pendapatan (descending)
        usort($rekapCabang, function($a, $b) {
            return $b['pendapatan'] <=> $a['pendapatan'];
        });

        // Data untuk chart (grafik)
        $chartData = [
            'labels' => collect($rekapCabang)->pluck('branch_name')->toArray(),
            'pendapatan' => collect($rekapCabang)->pluck('pendapatan')->toArray(),
            'laba' => collect($rekapCabang)->pluck('laba')->toArray(),
        ];

        // Data untuk trend harian (7 hari terakhir)
        $trendHarian = [];
        $endDate = Carbon::parse($tanggalAkhir);
        $startDate = Carbon::parse($tanggalAwal);
        $daysDiff = $startDate->diffInDays($endDate) + 1;
        $daysDiff = min($daysDiff, 30); // Maksimal 30 hari untuk chart

        for ($i = 0; $i < $daysDiff; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            $dayQuery = clone $query;
            $daySales = $dayQuery->whereDate('created_at', $date)->get();
            
            $dailyRevenue = 0;
            $dailyProfit = 0;
            
            foreach ($daySales as $sale) {
                foreach ($sale->items ?? [] as $item) {
                    $hargaJual = floatval($item->price ?? 0);
                    $hargaBeli = floatval($item->inventoryItem->purchase_price ?? 0);
                    $dailyRevenue += $hargaJual;
                    $dailyProfit += ($hargaJual - $hargaBeli);
                }
                foreach ($sale->accessories ?? [] as $acc) {
                    $hargaJual = floatval($acc->price ?? 0);
                    $hargaBeli = floatval($acc->purchaseAccessory->price ?? 0);
                    $dailyRevenue += $hargaJual;
                    $dailyProfit += ($hargaJual - $hargaBeli);
                }
            }
            
            $trendHarian[] = [
                'tanggal' => Carbon::parse($date)->format('d/m'),
                'pendapatan' => $dailyRevenue,
                'laba' => $dailyProfit
            ];
        }

        // Ambil data dropdown untuk filter
        $branches = Branch::all();
        $brands = Brand::all();

        return view('owner.laporan.index', compact(
            'rekapCabang',
            'totalSemua',
            'chartData',
            'trendHarian',
            'tanggalAwal',
            'tanggalAkhir',
            'branchId',
            'brandId',
            'branches',
            'brands'
        ));
    }
    public function detailCabang(Request $request)
    {
        $branchId = $request->branch_id;
        $tanggalAwal = $request->tanggal_awal;
        $tanggalAkhir = $request->tanggal_akhir;
        
        $branch = Branch::findOrFail($branchId);
        
        $query = Sale::with([
            'items.product.brand',
            'items.inventoryItem',
            'accessories.accessory',
            'accessories.purchaseAccessory',
            'customer'
        ])->where('branch_id', $branchId);
        
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
        
        $sales = $query->orderBy('created_at', 'desc')->get();
        
        return view('owner.laporan.detail-cabang', compact(
            'sales',
            'branch',
            'tanggalAwal',
            'tanggalAkhir'
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
        // Gunakan LIKE untuk pencarian partial
        $purchaseInventory = InventoryItem::with(['product', 'branch', 'purchaseItem.purchase'])
            ->where('imei', 'like', "%{$imei}%")
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
        // Gunakan LIKE untuk pencarian partial
        $sales = SaleItem::with(['sale', 'product', 'sale.branch'])
            ->where('imei', 'like', "%{$imei}%")
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
        // Gunakan LIKE untuk pencarian partial di whereHas
        $transferItems = StockTransferItem::with([
                'inventoryItem.product',
                'stockTransfer.fromBranch',
                'stockTransfer.toBranch'
            ])
            ->whereHas('inventoryItem', function ($q) use ($imei) {
                $q->where('imei', 'like', "%{$imei}%");
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
    public function ownerIndex()
    {
        $sales = Sale::with(['customer', 'branch'])
            ->latest()
            ->paginate(10);

        return view('owner.sales.index', compact('sales'));
    }
public function ownerLunas(Request $request)
{
    $query = Sale::with(['branch', 'customer', 'saleItems.product', 'saleAccessories.accessory']);

    // Filter cabang
    if ($request->branch_id) {
        $query->where('branch_id', $request->branch_id);
    }

    // Filter status
    if ($request->status_filter) {
        if ($request->status_filter == 'blm_lunas') {
            $query->where('status', 'belum lunas');
        } elseif ($request->status_filter == 'lunas') {
            $query->where('status', 'lunas');
        }
    }

    // Urutkan dari terbaru ke terlama
    $sales = $query->orderBy('created_at', 'desc')->paginate(20);
    
    $branches = Branch::all();

    // ===== PERBAIKAN PERHITUNGAN TOTAL =====
    // Hitung tanpa filter apapun untuk "Total Seluruh"
    $totalSeluruh = Sale::sum('total');
    
    // Hitung dengan filter yang aktif
    $queryFiltered = Sale::query();
    
    if ($request->branch_id) {
        $queryFiltered->where('branch_id', $request->branch_id);
    }
    
    // Total Belum Lunas (dengan filter cabang jika ada)
    $queryBelumLunas = clone $queryFiltered;
    $totalBelumLunas = $queryBelumLunas->where('status', 'belum lunas')->sum('total');
    
    // Total Lunas (dengan filter cabang jika ada)
    $queryLunas = clone $queryFiltered;
    $totalLunas = $queryLunas->where('status', 'lunas')->sum('total');

    return view('owner.sales.lunas', compact(
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
    // Method untuk menampilkan halaman edit penjualan (owner)
    public function ownerEdit($id)
    {
        $sale = Sale::with([
            'items.product',
            'items.inventoryItem',
            'items.accessory',
            'customer',
            'branch'
        ])->findOrFail($id);

        $customers = Customer::all();
            $itemsForJs = $sale->items->map(function ($i) {
            return [
                'type' => $i->imei ? 'Phone' : 'Accessory',
                'name' => $i->product->name ?? optional($i->accessory)->name,
                'imei' => $i->imei,
                'product_id' => $i->product_id,
                'accessory_id' => $i->accessory_id,
                'price' => $i->price,
                'modal' => optional($i->inventoryItem)->purchase_price ?? 0,
            ];
        });
        return view('owner.sales.edit', compact('sale', 'customers','itemsForJs'));
    }
    // Method untuk update penjualan (owner)
    public function ownerUpdate(Request $request, $id)
    {
        $sale = Sale::with('items.inventoryItem')->findOrFail($id);

        DB::transaction(function () use ($request, $sale) {

            // =============================
            // 🔴 JIKA ITEM KOSONG → HAPUS SALE
            // =============================
            if (!$request->has('items') || count($request->items) === 0) {

                // kembalikan stok HP
                foreach ($sale->items as $item) {
                    if ($item->inventoryItem) {
                        $item->inventoryItem->update([
                            'status' => 'in_stock'
                        ]);
                    }
                }

                // hapus item
                $sale->items()->delete();

                // hapus sale
                $sale->delete();

                return;
            }

            // =============================
            // 🔄 RESET ITEM LAMA (AMAN)
            // =============================
            foreach ($sale->items as $item) {
                if ($item->inventoryItem) {
                    $item->inventoryItem->update([
                        'status' => 'in_stock'
                    ]);
                }
            }

            $sale->items()->delete();

            // =============================
            // 🔄 UPDATE DATA SALE
            // =============================
            $sale->update([
                'customer_id' => $request->customer_id,
                'total' => collect($request->items)->sum('price'),
            ]);

            // =============================
            // ➕ SIMPAN ITEM BARU
            // =============================
            foreach ($request->items as $i) {

                // PHONE (IMEI)
                if (!empty($i['imei'])) {

                    $inventory = InventoryItem::where('imei', $i['imei'])
                        ->lockForUpdate()
                        ->firstOrFail();

                    $inventory->update(['status' => 'sold']);

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $inventory->product_id,
                        'imei' => $inventory->imei,
                        'price' => $i['price'],
                    ]);

                } 
                // ACCESSORY
                else {

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'accessory_id' => $i['accessory_id'],
                        'price' => $i['price'],
                    ]);
                }
            }
        });

        // ⬅️ jika sale dihapus
        if (!Sale::where('id', $id)->exists()) {
            return redirect()
                ->route('owner.sales.index')
                ->with('success', 'Penjualan berhasil dihapus karena tidak ada item.');
        }

        return redirect()
            ->route('owner.sales.index')
            ->with('success', 'Penjualan berhasil diperbarui.');
    }

}