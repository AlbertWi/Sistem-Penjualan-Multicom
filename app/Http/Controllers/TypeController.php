<?php

namespace App\Http\Controllers;

use App\Models\Type;
use App\Models\Brand;
use Illuminate\Http\Request;

class TypeController extends Controller
{
    public function index()
    {
        $types = Type::all();
        $brands = Brand::all();
        return view('manajer_operasional.types.index', compact('types','brands'));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:types,name',
            'brand_id' => 'required|exists:brands,id',
        ],[
            'name.required' => 'Nama Type harus diisi.',
            'brand_id.required' => 'Brand harus dipilih.',
        ]);

        Type::create($validated);

        return redirect()->route('manajer_operasional.types.index')
            ->with('success', 'Tipe berhasil ditambahkan.');
    }

    public function update(Request $request, Type $type)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:types,name,' . $type->id,
            'brand_id' => 'required|exists:brands,id',
        ],[
            'name.required' => 'Nama Type harus diisi.',
        ]);

        $type->update([
            'name' => $request->name,
            'brand_id' => $request->brand_id,
        ]);

        return redirect()->route('manajer_operasional.types.index')
            ->with('success', 'Tipe berhasil diperbarui.');
    }

}
