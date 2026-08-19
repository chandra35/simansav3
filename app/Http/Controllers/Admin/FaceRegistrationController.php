<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\AbsensiSetting;
use App\Models\FaceEncoding;
use App\Models\Gtk;
use App\Models\Siswa;
use App\Models\User;
use App\Services\FaceDescriptorService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Models\Activity;

class FaceRegistrationController extends Controller
{
    private const DUPLICATE_REQUIRED_CAPTURES = 3;

    public function index(Request $request)
    {
        $authUser = $request->user();
        $canManageAll = $this->canManageAllRegistrations($authUser);
        $selfFace = null;
        $selfHistory = collect();
        $selfCanRegister = false;

        if ($canManageAll) {
            $selectedType = $this->normalizeUserType($request->query('type'));
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
            $selfFace = FaceEncoding::where('user_id', $authUser->id)
                ->where('user_type', $selectedType)
                ->latest('created_at')
                ->first();
            $selfCanRegister = ! $selfFace || $selfFace->self_registration_unlocked_at !== null;

            if ($selfFace) {
                $selfHistory = Activity::query()
                    ->where('log_name', 'face-recognition')
                    ->where('subject_type', FaceEncoding::class)
                    ->where('subject_id', $selfFace->id)
                    ->with('causer:id,name')
                    ->latest()
                    ->limit(50)
                    ->get();
            }

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
                : 'Registrasi Wajah '.$this->typeLabel($selectedType),
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
            'selfRegistrant' => $selfOnly ? $registrants->first() : null,
            'selfFace' => $selfFace,
            'selfHistory' => $selfHistory,
            'selfCanRegister' => $selfCanRegister,
            'registeredCount' => $registeredCount,
            'verifiedCount' => $verifiedCount,
            'pendingCount' => max($registeredCount - $verifiedCount, 0),
        ]);
    }

    public function selfAttendanceHistory(Request $request)
    {
        $context = $this->getSelfRegistrationContext($request->user());
        abort_unless($context, 403, 'Riwayat presensi hanya tersedia untuk akun GTK atau siswa.');

        $month = min(max((int) $request->query('month', now()->month), 1), 12);
        $year = min(max((int) $request->query('year', now()->year), 2000), (int) now()->year + 1);
        $baseQuery = Absensi::query()
            ->where('user_id', $request->user()->id)
            ->where('user_type', $context['user_type']);
        $years = (clone $baseQuery)->selectRaw('YEAR(tanggal) as year')
            ->distinct()->orderByDesc('year')->pluck('year');
        if ($years->isEmpty()) {
            $years = collect([(int) now()->year]);
        }

        $monthlyQuery = (clone $baseQuery)
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month);
        $summaryRows = (clone $monthlyQuery)->get(['status', 'waktu_masuk', 'waktu_pulang']);
        $attendances = $monthlyQuery->with('location:id,nama')
            ->latest('tanggal')->paginate(31)->withQueryString();

        return view('admin.absensi.face-history-self', [
            'registrant' => $context['registrant'],
            'userType' => $context['user_type'],
            'month' => $month,
            'year' => $year,
            'years' => $years,
            'attendances' => $attendances,
            'summary' => [
                'recorded' => $summaryRows->count(),
                'present' => $summaryRows->where('status', 'hadir')->count(),
                'late' => $summaryRows->where('status', 'terlambat')->count(),
                'checked_out' => $summaryRows->whereNotNull('waktu_pulang')->count(),
            ],
        ]);
    }

    public function requestSelfRegistrationUnlock(Request $request)
    {
        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ]);
        $context = $this->getSelfRegistrationContext($request->user());
        abort_unless($context, 403, 'Permintaan ini hanya tersedia untuk akun GTK atau siswa.');

        $face = FaceEncoding::query()
            ->where('user_id', $request->user()->id)
            ->where('user_type', $context['user_type'])
            ->firstOrFail();

        if ($face->self_registration_unlocked_at) {
            return back()->with('info', 'Izin registrasi ulang sudah dibuka oleh admin.');
        }
        if ($face->self_registration_requested_at) {
            return back()->with('info', 'Permintaan registrasi ulang masih menunggu persetujuan admin.');
        }

        $face->update([
            'self_registration_requested_at' => now(),
            'self_registration_request_note' => trim((string) ($validated['note'] ?? '')) ?: null,
        ]);
        $this->logFaceActivity($face, 'self_registration_requested', 'self', 'Pengguna meminta izin registrasi ulang.');

        return back()->with('success', 'Permintaan registrasi ulang sudah dikirim dan menunggu persetujuan admin.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'user_type' => 'required|in:gtk,siswa',
            'descriptors' => 'required|array|min:3',
            'descriptors.*' => 'required|array',
            'angles' => 'required|array|min:3',
            'angles.*' => 'required|string',
            'quality_score' => 'nullable|numeric|min:0|max:100',
            'liveness_score' => 'nullable|numeric|min:0|max:100',
            'liveness_summary' => 'required|array',
            'photo' => 'nullable|string|max:3000000',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'required|string|max:1500000',
        ]);

        $authUser = $request->user();
        $canManageAll = $this->canManageAllRegistrations($authUser);
        $targetUser = User::with(['gtk:id,user_id', 'siswa:id,user_id'])->findOrFail($request->user_id);

        if ($canManageAll) {
            $userType = $request->user_type;
        } else {
            $context = $this->getSelfRegistrationContext($authUser);
            abort_unless($context, 403, 'Anda tidak memiliki akses ke fitur registrasi wajah.');
            abort_unless((string) $authUser->id === (string) $targetUser->id, 403, 'Anda hanya dapat mendaftarkan wajah milik akun sendiri.');

            $userType = $context['user_type'];
        }

        abort_unless($this->userMatchesType($targetUser, $userType), 422, 'Target registrasi tidak sesuai dengan tipe pengguna.');

        $existingFace = FaceEncoding::where('user_id', $targetUser->id)
            ->where('user_type', $userType)
            ->first();

        if (! $canManageAll && $existingFace && ! $existingFace->self_registration_unlocked_at) {
            return response()->json([
                'success' => false,
                'message' => 'Registrasi wajah sudah terkunci. Hubungi admin untuk meminta izin registrasi ulang.',
            ], 423);
        }

        $this->validateLivenessPayload($request);

        $duplicateMatch = $this->findDuplicateFaceMatch($request->descriptors, $targetUser->id);
        if ($duplicateMatch) {
            abort(422, $this->buildDuplicateMessage($duplicateMatch));
        }

        $photoPaths = collect($existingFace?->registration_photos ?: [$existingFace?->registration_photo])->filter()->values();
        if ($request->filled('photos')) {
            $photoPaths = collect($request->input('photos'))
                ->take(5)
                ->map(fn (string $photo, int $index) => $this->saveBase64Photo($photo, $targetUser->id, $userType, '-'.($index + 1)))
                ->values();
        } elseif ($request->photo) {
            $photoPaths = collect([$this->saveBase64Photo($request->photo, $targetUser->id, $userType)]);
        }
        $photoPath = $photoPaths->first();

        $faceData = FaceEncoding::updateOrCreate(
            ['user_id' => $targetUser->id, 'user_type' => $userType],
            [
                'descriptors' => $request->descriptors,
                'capture_angles' => $request->angles,
                'total_captures' => count($request->descriptors),
                'quality_score' => $request->input('quality_score', $request->input('liveness_score')),
                'registration_photo' => $photoPath,
                'registration_photos' => $photoPaths->all(),
                'self_registration_unlocked_at' => null,
                'self_registration_requested_at' => null,
                'self_registration_request_note' => null,
                'is_active' => true,
                'is_verified' => false,
                'verified_by' => null,
                'verified_at' => null,
            ]
        );

        $this->logFaceActivity(
            $faceData,
            $existingFace ? 'reregistered' : 'registered',
            $canManageAll ? 'admin' : 'self',
            $existingFace ? 'Registrasi wajah diperbarui.' : 'Registrasi wajah dibuat.'
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

    protected function validateLivenessPayload(Request $request): void
    {
        $summary = collect($request->input('liveness_summary', []));
        $completedSteps = collect($summary->get('completed_steps', []))
            ->filter(fn ($value) => is_string($value) && $value !== '')
            ->values();

        $requiredSteps = collect(['frontal', 'kedip', 'senyum']);
        $hasDirectionalTurn = $completedSteps->contains('kanan') || $completedSteps->contains('kiri');

        abort_if(
            $summary->get('challenge_count', 0) < 5 || count($request->input('angles', [])) < 5,
            422,
            'Registrasi wajah belum lengkap. Ulangi hingga semua tantangan selesai.'
        );

        abort_if(
            $requiredSteps->diff($completedSteps)->isNotEmpty() || ! $hasDirectionalTurn,
            422,
            'Deteksi liveness belum lengkap. Sistem membutuhkan kedipan, senyum, dan gerakan kepala asli.'
        );

        abort_if(
            (float) $summary->get('total_duration_ms', 0) < 4500,
            422,
            'Proses registrasi terlalu cepat untuk diverifikasi. Mohon ulangi dengan mengikuti instruksi kamera.'
        );

        abort_if(
            (int) $summary->get('blink_count', 0) < 1 || (int) $summary->get('max_blink_close_frames', 0) < 2,
            422,
            'Kedipan mata belum terdeteksi dengan jelas. Mohon ulangi registrasi dari kamera langsung.'
        );

        abort_if(
            (float) $summary->get('yaw_span', 0) < 0.30,
            422,
            'Gerakan kepala belum cukup berbeda. Jangan gunakan foto atau layar lain saat registrasi.'
        );

        abort_if(
            (float) $summary->get('smile_delta', 0) < 0.02,
            422,
            'Perubahan ekspresi belum cukup terbaca. Mohon ulangi dengan senyum natural di depan kamera.'
        );

        abort_if(
            (float) $summary->get('passive_motion_score', 0) < 0.012 || (float) $summary->get('gesture_motion_score', 0) < 0.05,
            422,
            'Gerakan liveness terdeteksi terlalu datar. Sistem menolak foto, layar, atau video replay.'
        );

        abort_if(
            (float) $request->input('liveness_score', $summary->get('liveness_score', 0)) < 65,
            422,
            'Skor liveness terlalu rendah. Mohon gunakan wajah asli di depan kamera dengan cahaya yang cukup.'
        );
    }

    public function verificationList(Request $request)
    {
        abort_unless($this->canManageAllRegistrations($request->user()), 403);

        $selectedType = $this->normalizeUserType($request->query('type'));

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
            ->orderBy('created_at', 'desc')
            ->get();

        $unlockRequests = (clone $baseQuery)
            ->whereNotNull('self_registration_requested_at')
            ->whereNull('self_registration_unlocked_at')
            ->orderBy('self_registration_requested_at')
            ->get();

        return view('admin.absensi.face-verification', [
            'pending' => $pending,
            'verified' => $verified,
            'allFaces' => $allFaces,
            'unlockRequests' => $unlockRequests,
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
            $event = 'approved';
        } else {
            $faceEncoding->update([
                'is_active' => false,
            ]);
            $message = 'Data wajah ditolak. Admin dapat meregistrasi ulang atau membuka izin registrasi ulang pengguna.';
            $event = 'rejected';
        }

        $this->logFaceActivity($faceEncoding, $event, 'admin', $message);

        return redirect()->route('admin.absensi.face-verification', [
            'type' => $faceEncoding->user_type,
        ])->with('success', $message);
    }

    public function destroy(FaceEncoding $faceEncoding)
    {
        abort_unless($this->canManageAllRegistrations(request()->user()), 403);

        $profile = $faceEncoding->user_type === 'gtk' ? $faceEncoding->user->gtk : $faceEncoding->user->siswa;
        $name = $profile->nama_lengkap ?? $faceEncoding->user->name ?? 'Unknown';
        $this->logFaceActivity($faceEncoding, 'deleted', 'admin', "Data wajah {$name} dihapus.");
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

        $this->logFaceActivity($faceEncoding, 'verification_reset', 'admin', 'Status verifikasi di-reset ke pending.');

        return redirect()->route('admin.absensi.face-verification', [
            'type' => $faceEncoding->user_type,
        ])->with('success', 'Status verifikasi di-reset ke pending.');
    }

    public function updateSelfRegistrationAccess(Request $request, FaceEncoding $faceEncoding)
    {
        abort_unless($this->canManageAllRegistrations($request->user()), 403);

        $validated = $request->validate(['action' => 'required|in:unlock,lock']);
        $isUnlock = $validated['action'] === 'unlock';
        $faceEncoding->update([
            'self_registration_unlocked_at' => $isUnlock ? now() : null,
            'self_registration_requested_at' => null,
            'self_registration_request_note' => null,
        ]);
        $this->logFaceActivity(
            $faceEncoding,
            $isUnlock ? 'self_registration_unlocked' : 'self_registration_locked',
            'admin',
            $isUnlock
                ? 'Admin membuka izin registrasi ulang dari akun pengguna.'
                : 'Admin membatalkan izin registrasi ulang dari akun pengguna.'
        );

        return redirect()->route('admin.absensi.face-verification', [
            'type' => $faceEncoding->user_type,
        ])->with('success', $isUnlock
            ? 'Izin registrasi ulang pengguna berhasil dibuka. Izin akan terkunci otomatis setelah registrasi berhasil.'
            : 'Izin registrasi ulang pengguna berhasil dibatalkan.');
    }

    public function getDescriptors(Request $request, FaceDescriptorService $service)
    {
        abort_unless($this->canManageAllRegistrations($request->user()), 403);
        $request->validate([
            'type' => 'required|in:gtk,siswa',
            'verified_only' => 'nullable|boolean',
        ]);
        $userType = $request->query('type');
        $verifiedOnly = $request->boolean('verified_only', false);

        return response()->json(['success' => true, 'data' => $service->forType($userType, $verifiedOnly)]);
    }

    private function saveBase64Photo(string $base64, string $userId, string $userType, string $suffix = ''): ?string
    {
        if (! preg_match('/^data:image\/(jpeg|jpg|png|webp);base64,/', $base64)) {
            throw ValidationException::withMessages(['photo' => 'Format foto registrasi tidak didukung.']);
        }

        $data = base64_decode(substr($base64, strpos($base64, ',') + 1), true);
        $imageInfo = $data === false ? false : getimagesizefromstring($data);
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (! $imageInfo || ! isset($extensions[$imageInfo['mime']]) || strlen($data) > 2 * 1024 * 1024) {
            throw ValidationException::withMessages(['photo' => 'Foto registrasi tidak valid atau melebihi 2 MB.']);
        }

        $filename = "face-registration/{$userType}/{$userId}{$suffix}.".$extensions[$imageInfo['mime']];
        Storage::disk('public')->put($filename, $data);

        return $filename;
    }

    private function logFaceActivity(FaceEncoding $faceEncoding, string $event, string $source, string $description): void
    {
        activity('face-recognition')
            ->performedOn($faceEncoding)
            ->causedBy(request()->user())
            ->withProperties([
                'event' => $event,
                'source' => $source,
                'user_id' => $faceEncoding->user_id,
                'user_type' => $faceEncoding->user_type,
                'registration_photo' => $faceEncoding->registration_photo,
                'quality_score' => $faceEncoding->quality_score,
                'total_captures' => $faceEncoding->total_captures,
            ])
            ->log($description);
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
                ->where('status_siswa', 'aktif')
                ->whereHas('user', fn ($user) => $user->where('is_active', true))
                ->orderBy('nama_lengkap')
                ->get(['id', 'user_id', 'nama_lengkap', 'nisn', 'foto_profile'])
                ->map(fn (Siswa $siswa) => $this->mapSiswaRegistrant($siswa))
                ->values();
        }

        return Gtk::active()->whereNotNull('user_id')
            ->whereHas('user')
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

    private function findDuplicateFaceMatch(array $submittedDescriptors, string $userId): ?array
    {
        $threshold = (float) AbsensiSetting::getValue('face_duplicate_threshold', 0.55);

        $candidateFaces = FaceEncoding::query()
            ->where('is_active', true)
            ->where('is_verified', true)
            ->where('user_id', '!=', $userId)
            ->with([
                'user:id,name',
                'user.gtk:id,user_id,nama_lengkap,nip',
                'user.siswa:id,user_id,nama_lengkap,nisn',
            ])
            ->get();

        $bestMatch = null;
        $minimumMatches = min(self::DUPLICATE_REQUIRED_CAPTURES, count($submittedDescriptors));

        foreach ($candidateFaces as $face) {
            if (empty($face->descriptors) || ! is_array($face->descriptors)) {
                continue;
            }

            $captureMatches = [];
            foreach ($submittedDescriptors as $submittedDescriptor) {
                if (! is_array($submittedDescriptor)) {
                    continue;
                }

                $nearestDistance = null;
                foreach ($face->descriptors as $storedDescriptor) {
                    if (! is_array($storedDescriptor)) {
                        continue;
                    }

                    $distance = $this->calculateDescriptorDistance($submittedDescriptor, $storedDescriptor);
                    if ($distance === null) {
                        continue;
                    }

                    if ($distance <= $threshold && ($nearestDistance === null || $distance < $nearestDistance)) {
                        $nearestDistance = $distance;
                    }
                }

                if ($nearestDistance !== null) {
                    $captureMatches[] = $nearestDistance;
                }
            }

            // Satu frame dapat terpengaruh blur, pose, atau cahaya. Duplikasi
            // hanya ditolak jika identitas yang sama cocok lintas beberapa capture.
            if (count($captureMatches) < $minimumMatches) {
                continue;
            }

            $averageDistance = array_sum($captureMatches) / count($captureMatches);
            if (! $bestMatch
                || count($captureMatches) > $bestMatch['matched_captures']
                || (count($captureMatches) === $bestMatch['matched_captures'] && $averageDistance < $bestMatch['distance'])) {
                $bestMatch = [
                    'distance' => $averageDistance,
                    'matched_captures' => count($captureMatches),
                    'face' => $face,
                ];
            }
        }

        return $bestMatch;
    }

    private function calculateDescriptorDistance(array $descriptorA, array $descriptorB): ?float
    {
        if (count($descriptorA) !== count($descriptorB) || empty($descriptorA)) {
            return null;
        }

        $sum = 0.0;
        foreach ($descriptorA as $index => $value) {
            if (! isset($descriptorB[$index]) || ! is_numeric($value) || ! is_numeric($descriptorB[$index])) {
                return null;
            }

            $difference = (float) $value - (float) $descriptorB[$index];
            $sum += $difference * $difference;
        }

        return sqrt($sum);
    }

    private function buildDuplicateMessage(array $duplicateMatch): string
    {
        /** @var FaceEncoding $face */
        $face = $duplicateMatch['face'];
        $profile = $face->user_type === 'siswa' ? $face->user?->siswa : $face->user?->gtk;
        $name = $profile?->nama_lengkap ?? $face->user?->name ?? 'akun lain';
        $identifier = $face->user_type === 'siswa' ? ($profile?->nisn ?? '-') : ($profile?->nip ?? '-');
        $identifierLabel = $face->user_type === 'siswa' ? 'NISN' : 'NIP';
        $status = $face->is_verified ? 'approved' : 'pending';

        return sprintf(
            'Kemiripan kuat terdeteksi pada %d capture dengan akun %s (%s: %s, status: %s). Registrasi dibatalkan untuk mencegah duplikasi.',
            $duplicateMatch['matched_captures'],
            $name,
            $identifierLabel,
            $identifier,
            $status
        );
    }
}
