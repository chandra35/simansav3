<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Gtk;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\JadwalPelajaran;
use App\Models\TahunPelajaran;
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
            ->select(['id', 'user_id', 'nisn', 'nama_lengkap', 'jenis_kelamin', 'nomor_hp', 'foto_profile', 'updated_at'])
            ->orderBy('id')
            ->paginate($filters['per_page'] ?? 100);

        $rows->through(fn (Siswa $siswa) => [
            'id' => $siswa->id, 'user_id' => $siswa->user_id, 'nisn' => $siswa->nisn, 'nama_lengkap' => $siswa->nama_lengkap,
            'jenis_kelamin' => $siswa->jenis_kelamin, 'nomor_hp' => $siswa->nomor_hp, 'email' => $siswa->user?->email,
            'photo_url' => $siswa->foto_profile_url, 'updated_at' => $siswa->updated_at?->toISOString(),
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

    public function roster(Request $request): JsonResponse
    {
        $years = TahunPelajaran::query()->orderByDesc('tahun_mulai')->get(['id','nama','semester_aktif','is_active','updated_at']);
        $classes = Kelas::query()->with(['tahunPelajaran:id,nama','siswaAktif:id,nisn,nama_lengkap'])->where('is_active', true)->get();
        $subjects = MataPelajaran::query()->where('is_active', true)->get(['id','nama_mapel','kode_mapel','updated_at']);
        $assignments = JadwalPelajaran::query()->with(['gtk:id,nama_lengkap','kelas:id,nama_kelas','mataPelajaran:id,nama_mapel'])->aktif()->get();
        return response()->json(['data' => ['academic_years'=>$years->map(fn($year)=>['id'=>$year->id,'name'=>$year->nama,'semester'=>$year->semester_aktif,'is_active'=>$year->is_active,'updated_at'=>$year->updated_at?->toISOString()])->values(),'classes' => $classes->map(fn ($class) => ['id'=>$class->id,'academic_year_id'=>$class->tahun_pelajaran_id,'name'=>$class->nama_lengkap,'code'=>$class->kode_kelas,'academic_year'=>$class->tahunPelajaran?->nama,'updated_at'=>$class->updated_at?->toISOString(),'members'=>$class->siswaAktif->map(fn ($student)=>['id'=>$student->id,'nisn'=>$student->nisn,'name'=>$student->nama_lengkap])->values()])->values(),'subjects'=>$subjects->map(fn ($subject)=>['id'=>$subject->id,'name'=>$subject->nama_mapel,'code'=>$subject->kode_mapel,'updated_at'=>$subject->updated_at?->toISOString()])->values(),'assignments'=>$assignments->map(fn ($row)=>['id'=>$row->id,'class_id'=>$row->kelas_id,'subject_id'=>$row->mapel_id,'teacher_id'=>$row->gtk_id,'semester'=>$row->semester,'academic_year'=>$row->tahunPelajaran?->nama,'updated_at'=>$row->updated_at?->toISOString()])->values()]]);
    }
}
