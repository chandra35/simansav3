<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\HasActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('username', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // Log login activity
            User::logCustomActivity('login', 'User login berhasil');
            
            $user = Auth::user();
            
            // Redirect based on user role
            if ($user->isSiswa()) {
                return redirect()->intended('siswa/dashboard');
            } else {
                return redirect()->intended('admin/dashboard');
            }
        }

        // Log failed login attempt
        User::logCustomActivity('login_failed', 'Percobaan login gagal dengan username: ' . $request->username);

        throw ValidationException::withMessages([
            'username' => ['Username atau password salah.'],
        ]);
    }

    public function logout(Request $request)
    {
        // Log logout activity
        User::logCustomActivity('logout', 'User logout');
        
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
