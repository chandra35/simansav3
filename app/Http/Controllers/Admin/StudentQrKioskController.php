<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentQrKioskController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view-student-attendance');

        return redirect()
            ->route('card-scanner.index')
            ->with('success', 'Gunakan Pemindai Kartu QR untuk scan ID Card siswa. Kelas dan sesi presensi akan ditentukan otomatis oleh sistem.');
    }
}
