<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\HasActivityLog;
use App\Services\ActivityLogService;
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
            
            $user = Auth::user();
            
            // Enhanced login log with device location if provided
            $logData = ['user_id' => $user->id];
            
            // Add device location if provided
            if ($request->filled('latitude') && $request->filled('longitude')) {
                $logData['latitude'] = $request->latitude;
                $logData['longitude'] = $request->longitude;
            }
            
            ActivityLogService::log(array_merge([
                'activity_type' => 'login',
                'description' => 'User logged in',
            ], $logData));
            
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
        // Enhanced logout log
        ActivityLogService::logLogout();
        
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
