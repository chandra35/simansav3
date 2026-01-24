<?php

namespace App\Http\Controllers\Admin\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\SiteSettings;
use App\Services\FacebookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Facades\LogActivity;

class SiteSettingsController extends Controller
{
    /**
     * Display the site settings form.
     */
    public function index()
    {
        $settings = SiteSettings::instance();
        return view('admin.ppdb.site-settings.index', compact('settings'));
    }

    /**
     * Update the site settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            // General
            'site_name' => 'required|string|max:255',
            'site_tagline' => 'nullable|string|max:500',
            'logo' => 'nullable|image|mimes:jpeg,jpg,png,svg|max:1024',
            'favicon' => 'nullable|image|mimes:ico,png|max:512',
            
            // Contact
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            
            // Social Media
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            
            // Facebook Integration
            'facebook_page_id' => 'nullable|string|max:100',
            'facebook_access_token' => 'nullable|string|max:1000',
            
            // Hero Section
            'hero_title' => 'nullable|string|max:255',
            'hero_subtitle' => 'nullable|string|max:500',
            'hero_image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'hero_button_text' => 'nullable|string|max:100',
            'hero_button_link' => 'nullable|string|max:255',
            
            // About Section
            'about_content' => 'nullable|string',
            'about_image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            
            // SEO
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            
            // Theme
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'accent_color' => 'nullable|string|max:7',
            
            // Maps
            'maps_latitude' => 'nullable|string|max:50',
            'maps_longitude' => 'nullable|string|max:50',
            'maps_embed' => 'nullable|string',
            
            // Registration
            'registration_open' => 'boolean',
            'registration_start' => 'nullable|date',
            'registration_end' => 'nullable|date|after_or_equal:registration_start',
            'registration_closed_message' => 'nullable|string|max:1000',
            
            // Footer
            'footer_text' => 'nullable|string|max:1000',
            'footer_copyright' => 'nullable|string|max:255',
        ]);

        $settings = SiteSettings::instance();
        
        $data = $request->except(['logo', 'favicon', 'hero_image', 'about_image', '_token', '_method']);
        $data['registration_open'] = $request->boolean('registration_open');

        // Handle file uploads
        $fileFields = [
            'logo' => 'site-settings',
            'favicon' => 'site-settings',
            'hero_image' => 'site-settings',
            'about_image' => 'site-settings',
        ];

        foreach ($fileFields as $field => $folder) {
            if ($request->hasFile($field)) {
                // Delete old file
                if ($settings->$field && Storage::disk('public')->exists($settings->$field)) {
                    Storage::disk('public')->delete($settings->$field);
                }

                $file = $request->file($field);
                $filename = time() . '_' . $field . '.' . $file->getClientOriginalExtension();
                $data[$field] = $file->storeAs($folder, $filename, 'public');
            }
        }

        $settings->update($data);

        // Log activity
        activity()
            ->performedOn($settings)
            ->causedBy(auth()->user())
            ->withProperties(['updated_fields' => array_keys($data)])
            ->log('Updated site settings');

        return redirect()->route('admin.ppdb.site-settings.index')
            ->with('success', 'Pengaturan berhasil disimpan.');
    }

    /**
     * Test Facebook connection.
     */
    public function testFacebook(Request $request)
    {
        try {
            $fbService = new FacebookService();
            $result = $fbService->checkConnection();
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Koneksi Facebook berhasil!',
                    'page_name' => $result['page_name'] ?? 'Unknown',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Gagal terhubung ke Facebook.',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Delete a specific image.
     */
    public function deleteImage(Request $request, $type)
    {
        $settings = SiteSettings::instance();
        $validTypes = ['logo', 'favicon', 'hero_image', 'about_image'];
        
        if (!in_array($type, $validTypes)) {
            return response()->json(['success' => false, 'message' => 'Invalid image type']);
        }

        if ($settings->$type && Storage::disk('public')->exists($settings->$type)) {
            Storage::disk('public')->delete($settings->$type);
        }

        $settings->update([$type => null]);

        return response()->json(['success' => true, 'message' => 'Gambar berhasil dihapus.']);
    }
}
