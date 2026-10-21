<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Laravel\Sanctum\PersonalAccessToken;

class RestApiController extends Controller
{
    /**
     * List of available abilities for SIMANSA REST API.
     */
    public const ABILITIES = [
        '*' => [
            'label' => '* (Akses Penuh / Bot Helper)',
            'description' => 'Akses ke semua endpoint API SIMANSA (Siswa, GTK, Jadwal, Presensi, Keuangan, Surat, LMS). Sangat disarankan untuk Bot OpenClaw WhatsApp.',
            'badge' => 'badge-danger',
        ],
        'siswa:read' => [
            'label' => 'siswa:read (Data Siswa)',
            'description' => 'Akses data siswa, profil lengkap, detail rombel/kelas, dan ringkasan statistik siswa.',
            'badge' => 'badge-primary',
        ],
        'gtk:read' => [
            'label' => 'gtk:read (Data Guru & Tendik)',
            'description' => 'Akses biodata guru, staf tata usaha, status sertifikasi, dan statistik GTK.',
            'badge' => 'badge-info',
        ],
        'jadwal:read' => [
            'label' => 'jadwal:read (Jadwal Pembelajaran)',
            'description' => 'Akses jadwal pelajaran hari ini, jadwal per kelas/rombel, dan jadwal per guru.',
            'badge' => 'badge-success',
        ],
        'presensi:read' => [
            'label' => 'presensi:read (Presensi & Sholat)',
            'description' => 'Akses rekap presensi harian siswa dan status pemantauan sholat berjamaah.',
            'badge' => 'badge-warning',
        ],
        'keuangan:read' => [
            'label' => 'keuangan:read (Keuangan & Tagihan)',
            'description' => 'Akses informasi tagihan siswa dan rekapitulasi pembayaran kas/SPP harian.',
            'badge' => 'badge-dark',
        ],
        'surat:read' => [
            'label' => 'surat:read (Tata Usaha & Persuratan)',
            'description' => 'Akses verifikasi surat keterangan aktif siswa dan arsip surat masuk.',
            'badge' => 'badge-secondary',
        ],
        'lms:read' => [
            'label' => 'lms:read (Sinkronisasi Data LMS)',
            'description' => 'Akses membaca data siswa dan guru aktif untuk sinkronisasi LMS / Moodle.',
            'badge' => 'badge-primary',
        ],
        'lms:auth' => [
            'label' => 'lms:auth (Autentikasi SSO LMS)',
            'description' => 'Memvalidasi kredensial login LMS langsung ke database SIMANSA.',
            'badge' => 'badge-primary',
        ],
        'lms:write' => [
            'label' => 'lms:write (Kirim Nilai LMS)',
            'description' => 'Menerima dan mencatat skor tugas / asesmen dari LMS.',
            'badge' => 'badge-primary',
        ],
    ];

    public function index(Request $request): View
    {
        $this->authorizeManager($request);

        $tokens = PersonalAccessToken::with('tokenable')
            ->latest()
            ->get();

        $users = User::select('id', 'name', 'username', 'email')
            ->with('roles:id,name')
            ->orderBy('name')
            ->get();

        return view('admin.pengaturan.rest-api', [
            'tokens' => $tokens,
            'users' => $users,
            'availableAbilities' => self::ABILITIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManager($request);

        $allowedKeys = implode(',', array_keys(self::ABILITIES));

        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'name' => ['required', 'string', 'max:100'],
            'abilities' => ['required', 'array', 'min:1'],
            'abilities.*' => ['string', "in:{$allowedKeys}"],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        $abilities = $data['abilities'];
        if (in_array('*', $abilities, true)) {
            $abilities = ['*'];
        }

        $targetUser = User::findOrFail($data['user_id']);
        $expiresAt = empty($data['expires_at']) ? null : Carbon::parse($data['expires_at']);
        $token = $targetUser->createToken($data['name'], $abilities, $expiresAt);

        return redirect()->route('admin.pengaturan.rest-api.index')->with([
            'success' => "Token API '{$data['name']}' berhasil dibuat untuk pengguna {$targetUser->name}.",
            'new_token' => $token->plainTextToken,
            'token_user' => $targetUser->name,
            'token_name' => $data['name'],
        ]);
    }

    public function destroy(Request $request, int $tokenId): RedirectResponse
    {
        $this->authorizeManager($request);

        $token = PersonalAccessToken::findOrFail($tokenId);
        $tokenName = $token->name;
        $token->delete();

        return redirect()->route('admin.pengaturan.rest-api.index')->with('success', "Token API '{$tokenName}' telah berhasil dicabut.");
    }

    private function authorizeManager(Request $request): void
    {
        abort_unless($request->user()?->can('manage-settings'), 403);
    }
}
