<?php

// app/Http/Controllers/AccessoryController.php
namespace App\Http\Controllers;

use App\Models\Accessory;
use Illuminate\Http\Request;

class AccessoryController extends Controller
{
    public function index()
    {
        $accessories = Accessory::latest()->paginate(10);
        return view('admin.accessories.index', compact('accessories'));
    }

    public function create()
    {
        return view('admin.accessories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Accessory::create($validated);

        return redirect()->route('accessories.index')->with('success', 'Accessory berhasil ditambahkan.');
    }

    public function edit(Accessory $accessory)
    {
        return view('admin.accessories.edit', compact('accessory'));
    }

    public function update(Request $request, Accessory $accessory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $accessory->update($validated);

        return redirect()->route('accessories.index')->with('success', 'Accessory berhasil diperbarui.');
    }

    public function destroy(Accessory $accessory)
    {
        $accessory->delete();
        return redirect()->route('accessories.index')->with('success', 'Accessory berhasil dihapus.');
    }
}
