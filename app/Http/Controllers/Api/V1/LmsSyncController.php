<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Gtk;
use App\Models\Siswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LmsSyncController extends Controller
{
    public function students(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:250'],
            'updated_since' => ['nullable', 'date'],
        ]);

        $rows = Siswa::query()
            ->with('user:id,email')
            ->where('status_siswa', 'aktif')
            // Samakan populasi sinkronisasi dengan metrik Dashboard: siswa
            // berstatus aktif yang benar-benar terdaftar pada rombel tahun ajaran aktif.
            ->whereHas('kelasTahunAktif')
            ->when($filters['updated_since'] ?? null, fn ($query, $updatedSince) => $query->where('updated_at', '>', $updatedSince))
            ->select(['id', 'user_id', 'nisn', 'nama_lengkap', 'jenis_kelamin', 'nik', 'tempat_lahir', 'tanggal_lahir', 'nomor_hp', 'alamat_siswa', 'npsn_asal_sekolah', 'foto_profile', 'updated_at'])
            ->orderBy('id')
            ->paginate($filters['per_page'] ?? 100);

        $rows->through(fn (Siswa $siswa) => [
            'id' => $siswa->id, 'user_id' => $siswa->user_id, 'nisn' => $siswa->nisn, 'nama_lengkap' => $siswa->nama_lengkap,
            'jenis_kelamin' => $siswa->jenis_kelamin, 'nik' => $siswa->nik, 'tempat_lahir' => $siswa->tempat_lahir,
            'tanggal_lahir' => $siswa->tanggal_lahir?->toDateString(), 'nomor_hp' => $siswa->nomor_hp, 'email' => $siswa->user?->email,
            'alamat' => $siswa->alamat_siswa, 'npsn' => $siswa->npsn_asal_sekolah, 'photo_url' => $siswa->foto_profile_url, 'updated_at' => $siswa->updated_at?->toISOString(),
        ]);

        return response()->json($rows);
    }

    public function teachers(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:250'],
            'updated_since' => ['nullable', 'date'],
        ]);

        $rows = Gtk::query()
            ->where('status_aktif', true)
            ->when($filters['updated_since'] ?? null, fn ($query, $updatedSince) => $query->where('updated_at', '>', $updatedSince))
            ->select(['id', 'user_id', 'nama_lengkap', 'nip', 'nuptk', 'nik', 'email', 'nomor_hp', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'alamat', 'jenis_ptk', 'jabatan', 'foto_profile', 'updated_at'])
            ->orderBy('id')
            ->paginate($filters['per_page'] ?? 100);

        $rows->through(fn (Gtk $gtk) => [
            'id' => $gtk->id, 'user_id' => $gtk->user_id, 'nama_lengkap' => $gtk->nama_lengkap, 'nip' => $gtk->nip,
            'nuptk' => $gtk->nuptk, 'nik' => $gtk->nik, 'email' => $gtk->email, 'nomor_hp' => $gtk->nomor_hp,
            'jenis_kelamin' => $gtk->jenis_kelamin, 'tempat_lahir' => $gtk->tempat_lahir, 'tanggal_lahir' => $gtk->tanggal_lahir?->toDateString(),
            'alamat' => $gtk->alamat, 'jenis_ptk' => $gtk->jenis_ptk, 'jabatan' => $gtk->jabatan, 'photo_url' => $gtk->foto_profile_url, 'updated_at' => $gtk->updated_at?->toISOString(),
        ]);

        return response()->json($rows);
    }
}
