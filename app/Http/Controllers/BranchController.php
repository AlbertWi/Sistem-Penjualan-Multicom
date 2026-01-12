<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::all();
        $onlineBranches = Branch::online()->get();
        $offlineBranches = Branch::offline()->get();
        
        return view('owner.branches.index', compact('branches', 'onlineBranches', 'offlineBranches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'branch_type' => 'required|in:online,offline'
        ],[
            'name.required' => 'Nama cabang harus diisi.',
            'branch_type.required' => 'Tipe cabang harus dipilih.',
            'branch_type.in' => 'Tipe cabang tidak valid.'
        ]);
        
        $latestBranch = Branch::orderBy('id', 'desc')->first();
        $nextId = $latestBranch ? $latestBranch->id + 1 : 1;
        $validated['code'] = 'BR' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
        
        Branch::create($validated);
        
        return redirect()->route('owner.branches.index')
            ->with('success', 'Cabang berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'branch_type' => 'required|in:online,offline'
        ],[
            'name.required' => 'Nama cabang harus diisi.',
            'branch_type.required' => 'Tipe cabang harus dipilih.',
            'branch_type.in' => 'Tipe cabang tidak valid.'
        ]);
        
        $validated['code'] = $branch->code;
        $branch->update($validated);
        
        return redirect()->route('owner.branches.index')
            ->with('success', 'Cabang berhasil diperbarui.');
    }
}