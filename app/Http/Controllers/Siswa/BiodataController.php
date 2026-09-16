<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Services\StudentBiodataPdfService;
use Illuminate\Http\Request;

class BiodataController extends Controller
{
    /** Cetak hanya biodata pemilik akun siswa yang sedang masuk. */
    public function print(Request $request, StudentBiodataPdfService $biodataPdf)
    {
        $this->authorize('print-siswa-biodata');

        $siswa = $request->user()?->siswa;
        abort_unless($siswa, 404, 'Data siswa tidak ditemukan.');

        return $biodataPdf->stream($siswa);
    }
}
