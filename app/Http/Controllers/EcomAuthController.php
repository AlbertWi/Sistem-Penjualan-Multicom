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
        ],[
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid. Contoh: user@example.com',
            'password.required' => 'Password Harus diisi',
        ]);
        

        if (Auth::guard('customer')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('catalog.index');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.'
        ])->withInput();
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
            'phone' => 'required|string|unique:customers,phone',
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
    public function profile()
    {
        $customer = auth('customer')->user();
        return view('ecom.profile', compact('customer'));
    }
    public function profileUpdate(Request $request)
    {
        $customer = auth('customer')->user();
        $request->validate([
            'name'  => 'required',
            'email' => 'required|email|unique:customers,email,' . $customer->id,
            'phone' => 'required|unique:customers,phone,' . $customer->id,
            'jenis_kelamin' => 'nullable|in:pria,wanita',
            'tanggal_lahir' => 'nullable|date',
            'password' => 'nullable|min:6',
        ]);

        $customer->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'jenis_kelamin' => $request->jenis_kelamin,
            'tanggal_lahir' => $request->tanggal_lahir,
        ]);

        /** jika password diganti */
        if ($request->filled('password')) {

            $request->validate([
                'current_password' => 'required',
            ]);

            if (!Hash::check($request->current_password, $customer->password)) {
                return back()->withErrors(['current_password' => 'Password lama tidak cocok']);
            }

            $customer->password = Hash::make($request->password);
            $customer->save();
        }

        return back()->with('success', 'Profile berhasil diperbarui');
    }
    public function editProfile()
    {
        $customer = auth('customer')->user();
        return view('ecom.profile', compact('customer'));
    }
}
