<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\BukuTamu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicBukuTamuController extends Controller
{
    public function index(): View
    {
        return view('public.buku-tamu.form', [
            'setting' => AppSetting::getInstance(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'alamat_instansi' => ['required', 'string', 'max:2000'],
            'nomor_hp' => ['required', 'string', 'max:30', 'regex:/^[0-9+() .-]+$/'],
            'keperluan' => ['required', 'string', 'max:2000'],
        ], [
            'nomor_hp.regex' => 'Nomor HP hanya boleh berisi angka dan karakter telepon yang umum.',
        ]);

        BukuTamu::create([
            'tanggal_kunjungan' => now()->toDateString(),
            ...$validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('public.buku-tamu.index')
            ->with('success', 'Data kunjungan berhasil dicatat. Terima kasih.');
    }
}
