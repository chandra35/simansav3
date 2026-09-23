<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\BukuTamu;
use App\Models\BukuTamuQrToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\Village;

class PublicBukuTamuController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('public.buku-tamu.token', $this->activeToken());
    }

    public function form(BukuTamuQrToken $token): View
    {
        abort_if($token->revoked_at, 404);

        return view('public.buku-tamu.form', [
            'setting' => AppSetting::getInstance(),
            'token' => $token,
            'provinces' => Province::orderBy('name')->get(['code', 'name']),
        ]);
    }

    public function store(Request $request, BukuTamuQrToken $token): RedirectResponse
    {
        abort_if($token->revoked_at, 404);

        $phone = preg_replace('/[\s().-]+/', '', (string) $request->input('nomor_hp'));
        if (str_starts_with($phone, '+62')) {
            $phone = '0'.substr($phone, 3);
        } elseif (str_starts_with($phone, '62')) {
            $phone = '0'.substr($phone, 2);
        }
        $request->merge(['nomor_hp' => $phone]);

        $validated = $request->validate([
            'jenis_tamu' => ['required', 'in:instansi,lembaga,individu'],
            'nama' => ['required', 'string', 'max:150'],
            'alamat_instansi' => ['nullable', 'required_if:jenis_tamu,instansi,lembaga', 'string', 'max:2000'],
            'nomor_hp' => ['required', 'string', 'regex:/^08[1-9][0-9]{7,10}$/'],
            'alamat' => ['required', 'string', 'max:2000'],
            'provinsi_code' => ['required', 'exists:indonesia_provinces,code'],
            'kota_code' => ['required', 'exists:indonesia_cities,code'],
            'kecamatan_code' => ['required', 'exists:indonesia_districts,code'],
            'kelurahan_code' => ['required', 'exists:indonesia_villages,code'],
            'keperluan' => ['required', 'string', 'max:2000'],
        ], [
            'nomor_hp.regex' => 'Masukkan nomor HP Indonesia yang valid, misalnya 081234567890 atau +6281234567890.',
        ]);

        BukuTamu::create([
            'tanggal_kunjungan' => now('Asia/Jakarta')->toDateString(),
            'buku_tamu_qr_token_id' => $token->id,
            ...$validated,
            'alamat_instansi' => $validated['alamat_instansi'] ?? '-',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('public.buku-tamu.token', $token)
            ->with('success', 'Data kunjungan berhasil dicatat.')
            ->with('success_meta', 'Terima kasih, data Anda sudah diterima oleh PTSP.');
    }

    public function cities(string $province)
    {
        return response()->json(City::where('province_code', $province)->orderBy('name')->get(['code', 'name']));
    }

    public function districts(string $city)
    {
        return response()->json(District::where('city_code', $city)->orderBy('name')->get(['code', 'name']));
    }

    public function villages(string $district)
    {
        return response()->json(Village::where('district_code', $district)->orderBy('name')->get(['code', 'name']));
    }

    private function activeToken(): BukuTamuQrToken
    {
        return BukuTamuQrToken::active()->latest('created_at')->first()
            ?: BukuTamuQrToken::create(['token' => Str::random(48)]);
    }
}
