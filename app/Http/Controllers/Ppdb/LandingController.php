<?php

namespace App\Http\Controllers\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use App\Models\Berita;
use App\Models\JadwalPpdb;
use App\Models\SiteSettings;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LandingController extends Controller
{
    public function index()
    {
        $settings = SiteSettings::instance();
        $sliders = Slider::active()->ordered()->get();
        $beritas = Berita::active()->latest()->take(6)->get();
        $jadwals = JadwalPpdb::active()->ordered()->get();
        
        return view('ppdb.landing', compact('settings', 'sliders', 'beritas', 'jadwals'));
    }

    public function beritaDetail($slug)
    {
        $settings = SiteSettings::instance();
        $berita = Berita::where('slug', $slug)->active()->firstOrFail();
        $berita->incrementViews();
        
        $relatedBeritas = Berita::active()
            ->where('id', '!=', $berita->id)
            ->when($berita->kategori, function ($query) use ($berita) {
                return $query->where('kategori', $berita->kategori);
            })
            ->latest()
            ->take(3)
            ->get();
        
        return view('ppdb.berita-detail', compact('settings', 'berita', 'relatedBeritas'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // Redirect based on role
        if ($user->hasRole(['Super Admin', 'Admin', 'Operator'])) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('Verifikator')) {
            return redirect()->route('verifikator.dashboard');
        } else {
            return redirect()->route('ppdb.dashboard');
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('ppdb.landing');
    }
}
