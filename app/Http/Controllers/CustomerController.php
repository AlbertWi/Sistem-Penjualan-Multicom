<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::latest()->paginate(10);
        return view('manajer_operasional.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('manajer_operasional.customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:191',
            'phone'          => 'nullable|string|max:20',
            'jenis_kelamin'  => 'nullable|in:L,P',
            'tanggal_lahir'  => 'nullable|date',
            'email'          => 'nullable|email|unique:customers,email',
            'password'       => 'nullable|min:6',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        Customer::create($validated);

        return redirect()
            ->route('manajer_operasional.customers.index')
            ->with('success', 'Customer berhasil ditambahkan.');
    }
    public function edit(Customer $customer)
    {
        return view('manajer_operasional.customers.edit', compact('customer'));
    }
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:191',
            'phone'          => 'nullable|string|max:20',
            'jenis_kelamin'  => 'nullable|in:pria,wanita',
            'tanggal_lahir'  => 'nullable|date',
            'email'          => 'nullable|email|unique:customers,email,' . $customer->id,
            'password'       => 'nullable|min:6',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $customer->update($validated);

        return redirect()
            ->route('manajer_operasional.customers.index')
            ->with('success', 'Customer berhasil diperbarui.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer berhasil dihapus.');
    }
}
