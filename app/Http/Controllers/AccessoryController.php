<?php
namespace App\Http\Controllers;

use App\Models\Accessory;
use Illuminate\Http\Request;

class AccessoryController extends Controller
{
    public function index()
    {
        $accessories = Accessory::latest()->paginate(10);
        return view('manajer_operasional.accessories.index', compact('accessories'));
    }

    public function create()
    {
        return view('manajer_operasional.accessories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Accessory::create($validated);

        return redirect()->route('manajer_operasional.accessories.index')->with('success', 'Aksesoris berhasil ditambahkan.');
    }

    public function edit(Accessory $accessory)
    {
        return view('manajer_operasional.accessories.edit', compact('accessory'));
    }

    public function update(Request $request, Accessory $accessory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $accessory->update($validated);

        return redirect()->route('manajer_operasional.accessories.index')->with('success', 'Aksesoris berhasil diperbarui.');
    }
}
