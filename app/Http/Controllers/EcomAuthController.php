<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class EcomAuthController extends Controller
{
    public function showLogin()
    {
        return view('ecom.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('customer')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('catalog.index');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah',
        ]);
    }

    public function showRegister()
    {
        return view('ecom.auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:customers,email',
            'password' => 'required|min:6|confirmed',
        ]);

        $data['password'] = Hash::make($data['password']);

        Customer::create($data);

        return redirect()->route('ecom.login')->with('success', 'Akun berhasil dibuat');
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('catalog.index');
    }
}
