<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FaceEncoding;
use App\Models\Gtk;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FaceRegistrationController extends Controller
{
    public function index(Request $request)
    {
        $authUser = $request->user();
        $canManageAll = $this->canManageAllRegistrations($authUser);

        if ($canManageAll) {
            $selectedType = $this->normalizeUserType($request->get('type', 'gtk'));
            $registrants = $this->getRegistrantsForType($selectedType);
            $storeUrl = route('admin.absensi.face-register.store');
            $selfOnly = false;
        } else {
            $context = $this->getSelfRegistrationContext($authUser);
            abort_unless($context, 403, 'Anda tidak memiliki akses ke fitur registrasi wajah.');

            $selectedType = $context['user_type'];
            $registrants = collect([$context['registrant']]);
            $storeUrl = $authUser->isSiswa()
                ? route('siswa.face-register.store')
                : route('admin.absensi.face-register.store');
            $selfOnly = true;
        }

        $faceMap = FaceEncoding::where('user_type', $selectedType)
            ->where('is_active', true)
            ->get()
            ->keyBy('user_id');

        $selectedUserId = $request->get('user_id');
        $initialSelection = $registrants->firstWhere('user_id', $selectedUserId);

        $registeredCount = $faceMap->count();
        $verifiedCount = $faceMap->where('is_verified', true)->count();

        return view('admin.absensi.face-register', [
            'pageTitle' => $selfOnly
                ? 'Registrasi Wajah Saya'
                : 'Registrasi Wajah ' . $this->typeLabel($selectedType),
            'subjectLabel' => $this->typeLabel($selectedType),
            'identifierLabel' => $selectedType === 'gtk' ? 'NIP' : 'NISN',
            'registrants' => $registrants,
            'faceMap' => $faceMap,
            'canManageAll' => $canManageAll,
            'selfOnly' => $selfOnly,
            'storeUrl' => $storeUrl,
            'selectedType' => $selectedType,
            'typeOptions' => $this->typeOptions(),
            'initialSelection' => $initialSelection,
            'registeredCount' => $registeredCount,
            'verifiedCount' => $verifiedCount,
            'pendingCount' => max($registeredCount - $verifiedCount, 0),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'user_type' => 'nullable|in:gtk,siswa',
            'descriptors' => 'required|array|min:3',
            'descriptors.*' => 'required|array',
            'angles' => 'required|array|min:3',
            'angles.*' => 'required|string',
            'quality_score' => 'nullable|numeric|min:0|max:100',
            'photo' => 'nullable|string',
        ]);

        $authUser = $request->user();
        $canManageAll = $this->canManageAllRegistrations($authUser);
        $targetUser = User::with(['gtk:id,user_id', 'siswa:id,user_id'])->findOrFail($request->user_id);

        if ($canManageAll) {
            $userType = $this->normalizeUserType(
                $request->input('user_type', $this->inferUserTypeFromUser($targetUser))
            );
        } else {
            $context = $this->getSelfRegistrationContext($authUser);
            abort_unless($context, 403, 'Anda tidak memiliki akses ke fitur registrasi wajah.');
            abort_unless((string) $authUser->id === (string) $targetUser->id, 403, 'Anda hanya dapat mendaftarkan wajah milik akun sendiri.');

            $userType = $context['user_type'];
        }

        abort_unless($this->userMatchesType($targetUser, $userType), 422, 'Target registrasi tidak sesuai dengan tipe pengguna.');

        if ($request->photo) {
            $this->saveBase64Photo($request->photo, $targetUser->id, $userType);
        }

        $faceData = FaceEncoding::updateOrCreate(
            ['user_id' => $targetUser->id, 'user_type' => $userType],
            [
                'descriptors' => $request->descriptors,
                'capture_angles' => $request->angles,
                'total_captures' => count($request->descriptors),
                'quality_score' => $request->quality_score,
                'is_active' => true,
                'is_verified' => false,
                'verified_by' => null,
                'verified_at' => null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Data wajah berhasil disimpan. Menunggu verifikasi admin.',
            'data' => [
                'id' => $faceData->id,
                'user_type' => $faceData->user_type,
                'total_captures' => $faceData->total_captures,
                'angles' => $faceData->capture_angles,
            ],
        ]);
    }

    public function verificationList(Request $request)
    {
        abort_unless($this->canManageAllRegistrations($request->user()), 403);

        $selectedType = $this->normalizeUserType($request->get('type', 'gtk'));

        $baseQuery = FaceEncoding::query()
            ->where('user_type', $selectedType)
            ->with([
                'user.gtk:id,user_id,nama_lengkap,nip,foto_profile',
                'user.siswa:id,user_id,nama_lengkap,nisn,foto_profile',
                'verifier:id,name',
            ]);

        $pending = (clone $baseQuery)
            ->where('is_verified', false)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        $verified = (clone $baseQuery)
            ->where('is_verified', true)
            ->orderBy('verified_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $allFaces = (clone $baseQuery)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.absensi.face-verification', [
            'pending' => $pending,
            'verified' => $verified,
            'allFaces' => $allFaces,
            'selectedType' => $selectedType,
            'subjectLabel' => $this->typeLabel($selectedType),
            'identifierLabel' => $selectedType === 'gtk' ? 'NIP' : 'NISN',
            'typeOptions' => $this->typeOptions(),
        ]);
    }

    public function verify(Request $request, FaceEncoding $faceEncoding)
    {
        abort_unless($this->canManageAllRegistrations($request->user()), 403);

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

        return redirect()->route('admin.absensi.face-verification', [
            'type' => $faceEncoding->user_type,
        ])->with('success', $message);
    }

    public function destroy(FaceEncoding $faceEncoding)
    {
        abort_unless($this->canManageAllRegistrations(request()->user()), 403);

        $profile = $faceEncoding->user_type === 'gtk' ? $faceEncoding->user->gtk : $faceEncoding->user->siswa;
        $name = $profile->nama_lengkap ?? $faceEncoding->user->name ?? 'Unknown';
        $faceEncoding->delete();

        return redirect()->route('admin.absensi.face-verification', [
            'type' => $faceEncoding->user_type,
        ])->with('success', "Data wajah {$name} berhasil dihapus.");
    }

    public function resetVerification(FaceEncoding $faceEncoding)
    {
        abort_unless($this->canManageAllRegistrations(request()->user()), 403);

        $faceEncoding->update([
            'is_verified' => false,
            'verified_by' => null,
            'verified_at' => null,
        ]);

        return redirect()->route('admin.absensi.face-verification', [
            'type' => $faceEncoding->user_type,
        ])->with('success', 'Status verifikasi di-reset ke pending.');
    }

    public function getDescriptors(Request $request)
    {
        $userType = $this->normalizeUserType($request->get('type', 'gtk'));
        $verifiedOnly = $request->boolean('verified_only', false);

        $query = FaceEncoding::where('user_type', $userType)
            ->where('is_active', true);

        if ($verifiedOnly) {
            $query->where('is_verified', true);
        }

        $faces = $query->with([
            'user:id,name',
            'user.gtk:id,user_id,nama_lengkap,nip,foto_profile',
            'user.siswa:id,user_id,nama_lengkap,nisn,foto_profile',
        ])->get()->map(function ($face) {
            $userName = $face->user->name ?? 'Unknown';
            $gtk = $face->user->gtk ?? null;
            $siswa = $face->user->siswa ?? null;
            $profile = $face->user_type === 'gtk' ? $gtk : $siswa;

            return [
                'user_id' => $face->user_id,
                'user_type' => $face->user_type,
                'name' => $profile?->nama_lengkap ?? $userName,
                'identifier' => $face->user_type === 'gtk' ? $gtk?->nip : $siswa?->nisn,
                'foto' => $profile?->foto_profile_url,
                'descriptors' => $face->descriptors,
            ];
        });

        return response()->json(['success' => true, 'data' => $faces]);
    }

    private function saveBase64Photo(string $base64, string $userId, string $userType): ?string
    {
        if (!preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
            return null;
        }

        $extension = $matches[1];
        $data = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64));
        if ($data === false) {
            return null;
        }

        $filename = "face-registration/{$userType}/{$userId}." . $extension;
        Storage::disk('public')->put($filename, $data);

        return $filename;
    }

    private function canManageAllRegistrations(User $user): bool
    {
        return $user->can('face-registration-admin');
    }

    private function getSelfRegistrationContext(User $user): ?array
    {
        if ($user->gtk) {
            return [
                'user_type' => 'gtk',
                'registrant' => $this->mapGtkRegistrant($user->gtk),
            ];
        }

        if ($user->siswa) {
            return [
                'user_type' => 'siswa',
                'registrant' => $this->mapSiswaRegistrant($user->siswa),
            ];
        }

        return null;
    }

    private function getRegistrantsForType(string $userType): Collection
    {
        if ($userType === 'siswa') {
            return Siswa::whereNotNull('user_id')
                ->orderBy('nama_lengkap')
                ->get(['id', 'user_id', 'nama_lengkap', 'nisn', 'foto_profile'])
                ->map(fn (Siswa $siswa) => $this->mapSiswaRegistrant($siswa))
                ->values();
        }

        return Gtk::whereNotNull('user_id')
            ->orderBy('nama_lengkap')
            ->get(['id', 'user_id', 'nama_lengkap', 'nip', 'foto_profile'])
            ->map(fn (Gtk $gtk) => $this->mapGtkRegistrant($gtk))
            ->values();
    }

    private function mapGtkRegistrant(Gtk $gtk): array
    {
        return [
            'record_id' => $gtk->id,
            'user_id' => $gtk->user_id,
            'name' => $gtk->nama_lengkap,
            'identifier' => $gtk->nip,
            'avatar_url' => $gtk->foto_profile_url,
            'user_type' => 'gtk',
        ];
    }

    private function mapSiswaRegistrant(Siswa $siswa): array
    {
        return [
            'record_id' => $siswa->id,
            'user_id' => $siswa->user_id,
            'name' => $siswa->nama_lengkap,
            'identifier' => $siswa->nisn,
            'avatar_url' => $siswa->foto_profile_url,
            'user_type' => 'siswa',
        ];
    }

    private function normalizeUserType(?string $userType): string
    {
        return in_array($userType, ['gtk', 'siswa'], true) ? $userType : 'gtk';
    }

    private function inferUserTypeFromUser(User $user): string
    {
        return $user->siswa ? 'siswa' : 'gtk';
    }

    private function userMatchesType(User $user, string $userType): bool
    {
        return $userType === 'siswa' ? (bool) $user->siswa : (bool) $user->gtk;
    }

    private function typeLabel(string $userType): string
    {
        return $userType === 'siswa' ? 'Siswa' : 'GTK';
    }

    private function typeOptions(): array
    {
        return [
            'gtk' => 'GTK',
            'siswa' => 'Siswa',
        ];
    }
}
