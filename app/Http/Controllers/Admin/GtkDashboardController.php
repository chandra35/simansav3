<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gtk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GtkDashboardController extends Controller
{
    /**
     * Display GTK dashboard
     */
    public function index()
    {
        $this->authorize('view-gtk-dashboard');

        $user = Auth::user();
        $gtk = $user->gtk;

        // If GTK record doesn't exist, create one
        if (!$gtk) {
            $gtk = Gtk::create([
                'user_id' => $user->id,
                'nama_lengkap' => $user->name,
                'nik' => $user->username,
                'jenis_kelamin' => 'L', // Default, will be updated in profile
                'created_by' => $user->id,
            ]);
        }

        // Check if user needs to change password (first login)
        if ($user->is_first_login) {
            return redirect()->route('admin.gtk.profile.password')
                ->with('info', 'Silakan ganti password Anda terlebih dahulu untuk keamanan akun.');
        }

        // Check if profile is incomplete
        $needsCompletion = !$gtk->data_diri_completed || !$gtk->data_kepeg_completed;

        // Get statistics for GTK
        $stats = [
            'data_diri_completed' => $gtk->data_diri_completed,
            'data_kepeg_completed' => $gtk->data_kepeg_completed,
            'completion_percentage' => $this->calculateCompletionPercentage($gtk),
        ];

        return view('admin.gtk.dashboard', compact('gtk', 'stats', 'needsCompletion'));
    }

    /**
     * Calculate profile completion percentage
     */
    private function calculateCompletionPercentage($gtk)
    {
        $total = 0;
        $completed = 0;

        // Data Diri fields (wajib)
        $dataDiriFields = ['nama_lengkap', 'nik', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 
                           'provinsi_id', 'kabupaten_id', 'kecamatan_id', 'kelurahan_id', 'alamat'];
        foreach ($dataDiriFields as $field) {
            $total++;
            if (!empty($gtk->$field)) {
                $completed++;
            }
        }

        // Data Kepegawaian fields (wajib: status_kepegawaian, jabatan saja. NUPTK & TMT tidak wajib)
        $dataKepegFields = ['status_kepegawaian', 'jabatan'];
        foreach ($dataKepegFields as $field) {
            $total++;
            if (!empty($gtk->$field)) {
                $completed++;
            }
        }

        return $total > 0 ? round(($completed / $total) * 100) : 0;
    }
}
