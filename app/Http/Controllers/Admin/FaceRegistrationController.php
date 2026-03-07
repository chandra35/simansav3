<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FaceEncoding;
use App\Models\Gtk;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FaceRegistrationController extends Controller
{
    /**
     * Halaman registrasi wajah
     */
    public function index()
    {
        // GTK list with face registration status
        $gtkList = Gtk::whereNotNull('user_id')
            ->orderBy('nama_lengkap')
            ->get(['id', 'user_id', 'nama_lengkap', 'nip']);

        // Face data indexed by user_id
        $faceMap = FaceEncoding::where('user_type', 'gtk')
            ->where('is_active', true)
            ->get()
            ->keyBy('user_id');

        return view('admin.absensi.face-register', compact('gtkList', 'faceMap'));
    }

    /**
     * Simpan face descriptors
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'descriptors' => 'required|array|min:3',
            'descriptors.*' => 'required|array',
            'angles' => 'required|array|min:3',
            'angles.*' => 'required|string',
            'quality_score' => 'nullable|numeric|min:0|max:100',
            'photo' => 'nullable|string',
        ]);

        $userId = $request->user_id;

        // Simpan foto thumbnail jika ada
        $photoPath = null;
        if ($request->photo) {
            $photoPath = $this->saveBase64Photo($request->photo, $userId);
        }

        $faceData = FaceEncoding::updateOrCreate(
            ['user_id' => $userId, 'user_type' => 'gtk'],
            [
                'descriptors' => $request->descriptors,
                'capture_angles' => $request->angles,
                'total_captures' => count($request->descriptors),
                'quality_score' => $request->quality_score,
                'is_active' => true,
                'is_verified' => false,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Data wajah berhasil disimpan. Menunggu verifikasi admin.',
            'data' => [
                'id' => $faceData->id,
                'total_captures' => $faceData->total_captures,
                'angles' => $faceData->capture_angles,
            ],
        ]);
    }

    /**
     * Admin: daftar face encoding yang perlu diverifikasi
     */
    public function verificationList()
    {
        $pending = FaceEncoding::where('is_verified', false)
            ->where('is_active', true)
            ->with(['user.gtk:id,user_id,nama_lengkap,nip'])
            ->orderBy('created_at', 'desc')
            ->get();

        $verified = FaceEncoding::where('is_verified', true)
            ->with(['user.gtk:id,user_id,nama_lengkap,nip', 'verifier:id,name'])
            ->orderBy('verified_at', 'desc')
            ->paginate(20);

        // Semua face records (untuk tab "Semua Data")
        $allFaces = FaceEncoding::where('is_active', true)
            ->with(['user.gtk:id,user_id,nama_lengkap,nip', 'verifier:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.absensi.face-verification', compact('pending', 'verified', 'allFaces'));
    }

    /**
     * Admin: verifikasi face encoding
     */
    public function verify(Request $request, FaceEncoding $faceEncoding)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
        ]);

        if ($request->action === 'approve') {
            $faceEncoding->update([
                'is_verified' => true,
                'verified_by' => Auth::id(),
                'verified_at' => now(),
            ]);
            $message = 'Data wajah berhasil diverifikasi.';
        } else {
            $faceEncoding->update([
                'is_active' => false,
            ]);
            $message = 'Data wajah ditolak. User dapat mendaftar ulang.';
        }

        return redirect()->route('admin.absensi.face-verification')
            ->with('success', $message);
    }

    /**
     * Admin: hapus data wajah
     */
    public function destroy(FaceEncoding $faceEncoding)
    {
        $name = $faceEncoding->user->gtk->nama_lengkap ?? $faceEncoding->user->name ?? 'Unknown';
        $faceEncoding->delete();

        return redirect()->route('admin.absensi.face-verification')
            ->with('success', "Data wajah {$name} berhasil dihapus.");
    }

    /**
     * Admin: reset verifikasi (set pending kembali)
     */
    public function resetVerification(FaceEncoding $faceEncoding)
    {
        $faceEncoding->update([
            'is_verified' => false,
            'verified_by' => null,
            'verified_at' => null,
        ]);

        return redirect()->route('admin.absensi.face-verification')
            ->with('success', 'Status verifikasi di-reset ke pending.');
    }

    /**
     * API: Get all active face descriptors (for kiosk matching)
     */
    public function getDescriptors(Request $request)
    {
        $userType = $request->get('type', 'gtk');

        $faces = FaceEncoding::where('user_type', $userType)
            ->where('is_active', true)
            ->where('is_verified', true)
            ->with('user:id,name')
            ->get()
            ->map(function ($face) {
                $userName = $face->user->name ?? 'Unknown';
                // Get GTK/Siswa data for richer display
                $gtk = $face->user->gtk ?? null;

                return [
                    'user_id' => $face->user_id,
                    'name' => $gtk ? $gtk->nama_lengkap : $userName,
                    'nip' => $gtk?->nip,
                    'foto' => $gtk?->foto_profile_url,
                    'descriptors' => $face->descriptors,
                ];
            });

        return response()->json(['success' => true, 'data' => $faces]);
    }

    /**
     * Save base64 photo to storage
     */
    private function saveBase64Photo(string $base64, string $userId): ?string
    {
        if (!preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
            return null;
        }

        $extension = $matches[1];
        $data = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64));
        if ($data === false) {
            return null;
        }

        $filename = "face-registration/{$userId}." . $extension;
        \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $data);

        return $filename;
    }
}
