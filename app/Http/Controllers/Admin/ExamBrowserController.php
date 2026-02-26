<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamBrowserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExamBrowserController extends Controller
{
    /**
     * Display exam browser settings page.
     */
    public function index()
    {
        $setting = ExamBrowserSetting::getActive();
        
        // Create default if none exists
        if (!$setting) {
            $setting = ExamBrowserSetting::create([
                'app_name' => 'ExamAnmet',
                'school_name' => 'MAN 1 Metro',
                'moodle_url' => 'https://elearning.man1metro.sch.id',
                'user_agent' => 'SEB/3.0 ExamAnmet/1.0',
                'is_active' => true,
                'created_by' => Auth::id(),
            ]);
        }

        return view('admin.exam-browser.index', compact('setting'));
    }

    /**
     * Update exam browser settings.
     */
    public function update(Request $request)
    {
        $setting = ExamBrowserSetting::getActive();
        
        if (!$setting) {
            return redirect()->route('admin.exam-browser.index')
                ->with('error', 'Pengaturan tidak ditemukan.');
        }

        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'school_name' => 'required|string|max:255',
            'moodle_url' => 'required|url|max:500',
            'user_agent' => 'required|string|max:500',
            'app_password' => 'nullable|string|max:255',
            'exit_password' => 'nullable|string|max:255',
            'seb_config_key' => 'nullable|string',
            'seb_exam_key' => 'nullable|string',
            'allow_screenshot' => 'boolean',
            'allow_clipboard' => 'boolean',
            'allow_navigation' => 'boolean',
            'allow_reload' => 'boolean',
            'show_toolbar' => 'boolean',
            'is_active' => 'boolean',
            'allowed_urls' => 'nullable|string',
            'blocked_apps' => 'nullable|string',
            'custom_css' => 'nullable|string',
            'custom_js' => 'nullable|string',
            'minimum_app_version' => 'nullable|string|max:20',
            'announcement' => 'nullable|string',
            'app_logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        // Handle logo upload
        if ($request->hasFile('app_logo')) {
            // Delete old logo
            if ($setting->app_logo_path) {
                Storage::disk('public')->delete($setting->app_logo_path);
            }
            $path = $request->file('app_logo')->store('exam-browser', 'public');
            $validated['app_logo_path'] = $path;
        }

        // Handle checkboxes (unchecked = not sent)
        $validated['allow_screenshot'] = $request->boolean('allow_screenshot');
        $validated['allow_clipboard'] = $request->boolean('allow_clipboard');
        $validated['allow_navigation'] = $request->boolean('allow_navigation');
        $validated['allow_reload'] = $request->boolean('allow_reload');
        $validated['show_toolbar'] = $request->boolean('show_toolbar');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['updated_by'] = Auth::id();

        // Remove logo file from validated (already handled)
        unset($validated['app_logo']);

        $setting->update($validated);

        return redirect()->route('admin.exam-browser.index')
            ->with('success', 'Pengaturan Exam Browser berhasil diperbarui!');
    }

    /**
     * Delete logo
     */
    public function deleteLogo()
    {
        $setting = ExamBrowserSetting::getActive();
        
        if ($setting && $setting->app_logo_path) {
            Storage::disk('public')->delete($setting->app_logo_path);
            $setting->update(['app_logo_path' => null]);
        }

        return redirect()->route('admin.exam-browser.index')
            ->with('success', 'Logo berhasil dihapus.');
    }

    /**
     * Generate SEB Config Key
     */
    public function generateSebKey()
    {
        $setting = ExamBrowserSetting::getActive();
        
        if (!$setting) {
            return response()->json(['error' => 'Setting tidak ditemukan'], 404);
        }

        // Generate SEB-compatible config key hash
        $configData = json_encode([
            'url' => $setting->moodle_url,
            'user_agent' => $setting->user_agent,
            'timestamp' => now()->timestamp,
        ]);
        
        $sebConfigKey = hash('sha256', $configData);
        $setting->update([
            'seb_config_key' => $sebConfigKey,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'seb_config_key' => $sebConfigKey,
            'message' => 'SEB Config Key berhasil di-generate!',
        ]);
    }

    /**
     * Preview config as JSON (for debugging)
     */
    public function previewConfig()
    {
        $setting = ExamBrowserSetting::getActive();
        
        if (!$setting) {
            return response()->json(['error' => 'Tidak ada konfigurasi aktif'], 404);
        }

        return response()->json($setting->toApiConfig());
    }
}
