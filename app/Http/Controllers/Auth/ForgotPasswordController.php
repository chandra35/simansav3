<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

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

        // Delete old tokens
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        // Insert new token
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($token),
            'created_at' => Carbon::now(),
        ]);

        // Send email using EmailService with template
        $resetLink = route('password.reset', ['token' => $token, 'email' => $user->email]);
        $result = $emailService->sendPasswordReset($user->email, $user->name, $resetLink);

        if ($result['success']) {
            User::logCustomActivity('password_reset_request', 'Request reset password untuk email: ' . $user->email);
            return back()->with('status', 'Link reset password telah dikirim ke email Anda. Silakan cek inbox atau folder spam.');
        } else {
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
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
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
            'password' => 'required|min:8|confirmed',
        ]);

        // Check token
        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$tokenRecord) {
            return back()->withErrors(['email' => 'Token tidak valid atau sudah expired.']);
        }

        // Check if token is expired (60 minutes)
        $createdAt = Carbon::parse($tokenRecord->created_at);
        if (Carbon::now()->diffInMinutes($createdAt) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'Token sudah expired. Silakan request ulang.']);
        }

        // Verify token
        if (!Hash::check($request->token, $tokenRecord->token)) {
            return back()->withErrors(['email' => 'Token tidak valid.']);
        }

        // Find user
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email tidak ditemukan.']);
        }

        // Update password
        $user->readable_password = $request->password; // Store encrypted readable password
        $user->update([
            'password' => Hash::make($request->password),
            'is_first_login' => false, // Mark as already changed password
        ]);

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
}
