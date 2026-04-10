<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /**
     * Default email domain yang tidak bisa digunakan untuk reset password
     */
    protected $defaultEmailDomains = [
        '@siswa.simansa.sch.id',
        '@student.simansa.sch.id',
        '@siswa.ma.sch.id',
    ];

    /**
     * Show forgot password form
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Check if email is a default/system generated email
     */
    protected function isDefaultEmail(string $email): bool
    {
        $email = strtolower($email);
        foreach ($this->defaultEmailDomains as $domain) {
            if (Str::endsWith($email, strtolower($domain))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Send password reset link
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Check if SMTP is enabled
        $emailService = new EmailService();
        if (!$emailService->isConfigured()) {
            return back()->withErrors([
                'email' => 'Fitur reset password via email belum aktif. Silakan hubungi administrator/operator.'
            ])->withInput();
        }

        // Check if email is using default domain
        if ($this->isDefaultEmail($request->email)) {
            return back()->withErrors([
                'email' => 'Email yang Anda masukkan adalah email default sistem (@siswa.simansa.sch.id). Untuk reset password, silakan hubungi Operator Sekolah. Atau update email Anda melalui menu profil setelah login.'
            ])->withInput();
        }

        // Find user
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Don't reveal if email exists or not for security
            return back()->with('status', 'Jika email terdaftar, kami telah mengirimkan link reset password.');
        }

        // Double check - verify user's email is not default
        if ($this->isDefaultEmail($user->email)) {
            return back()->withErrors([
                'email' => 'Email ini adalah email default sistem. Silakan hubungi Operator Sekolah untuk reset password.'
            ])->withInput();
        }

        // Generate token
        $token = Str::random(64);
        $createdAt = Carbon::now();

        // Upsert token to avoid duplicate-key race when users click repeatedly.
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => $createdAt,
            ]
        );

        // Send email using EmailService with template
        $resetLink = route('password.reset', ['token' => $token, 'email' => $user->email]);
        $result = $emailService->sendPasswordReset($user->email, $user->name, $resetLink);

        if ($result['success']) {
            User::logCustomActivity('password_reset_request', 'Request reset password untuk email: ' . $user->email);
            return back()->with('status', 'Link reset password telah dikirim ke email Anda. Silakan cek inbox atau folder spam.');
        } else {
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();

            return back()->withErrors([
                'email' => 'Gagal mengirim email: ' . $result['message']
            ])->withInput();
        }
    }

    /**
     * Show reset password form
     */
    public function showResetForm(Request $request, $token = null)
    {
        $email = $request->query('email');

        if (blank($token) || blank($email)) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Link reset password tidak lengkap atau tidak valid.']);
        }

        $tokenRecord = $this->getValidTokenRecord($email, $token);
        if (!$tokenRecord) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Link reset password tidak valid atau sudah kedaluwarsa. Silakan minta link baru.']);
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * Reset password
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|string|same:password',
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password_confirmation.required' => 'Konfirmasi password baru wajib diisi.',
            'password_confirmation.same' => 'Konfirmasi password baru tidak sesuai.',
        ]);

        // Check token
        $tokenRecord = $this->getValidTokenRecord($request->email, $request->token);
        if (!$tokenRecord) {
            return back()->withErrors(['email' => 'Link reset password tidak valid atau sudah kedaluwarsa. Silakan request ulang.']);
        }

        // Find user
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email tidak ditemukan.']);
        }

        // Update password and keep readable string in sync for admin support flows.
        $user->password = Hash::make($request->password);
        $user->is_first_login = false;
        $user->readable_password = $request->password;
        $user->save();

        // Delete token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        User::logCustomActivity('password_reset_complete', 'Password berhasil direset untuk email: ' . $user->email);

        // Send notification email about password change
        $emailService = new EmailService();
        if ($emailService->isConfigured()) {
            $emailService->sendPasswordChanged($user->email, $user->name);
        }

        return redirect()->route('login')
            ->with('status', 'Password Anda berhasil diubah! Silakan login menggunakan password baru Anda.');
    }

    private function getValidTokenRecord(string $email, string $plainToken): ?object
    {
        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$tokenRecord) {
            return null;
        }

        $createdAt = Carbon::parse($tokenRecord->created_at);
        if (Carbon::now()->diffInMinutes($createdAt) > 60) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return null;
        }

        if (!Hash::check($plainToken, $tokenRecord->token)) {
            return null;
        }

        return $tokenRecord;
    }
}
