<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSettings extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'site_settings';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        // General
        'site_name',
        'site_tagline',
        'logo',
        'favicon',
        
        // Contact
        'email',
        'phone',
        'whatsapp',
        'address',
        
        // Social Media
        'facebook_url',
        'instagram_url',
        'youtube_url',
        'twitter_url',
        
        // Facebook Integration
        'facebook_page_id',
        'facebook_access_token',
        
        // Hero Section
        'hero_title',
        'hero_subtitle',
        'hero_image',
        'hero_button_text',
        'hero_button_link',
        
        // About Section
        'about_content',
        'about_image',
        
        // SEO
        'meta_title',
        'meta_description',
        'meta_keywords',
        
        // Theme
        'primary_color',
        'secondary_color',
        'accent_color',
        
        // Maps
        'maps_latitude',
        'maps_longitude',
        'maps_embed',
        
        // Registration
        'registration_open',
        'registration_start',
        'registration_end',
        'registration_closed_message',
        
        // Footer
        'footer_text',
        'footer_copyright',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'registration_open' => 'boolean',
        'registration_start' => 'date',
        'registration_end' => 'date',
    ];

    /**
     * Cache key for site settings.
     */
    const CACHE_KEY = 'site_settings';

    /**
     * Cache duration in seconds (1 hour).
     */
    const CACHE_DURATION = 3600;

    /**
     * Get the singleton instance of site settings.
     */
    public static function instance()
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_DURATION, function () {
            $settings = self::first();
            
            if (!$settings) {
                $settings = self::create([
                    'site_name' => 'PPDB Online',
                    'site_tagline' => 'Pendaftaran Peserta Didik Baru',
                    'hero_title' => 'Selamat Datang di PPDB Online',
                    'hero_subtitle' => 'Sistem Pendaftaran Peserta Didik Baru Online',
                    'hero_button_text' => 'Daftar Sekarang',
                    'primary_color' => '#007bff',
                    'secondary_color' => '#6c757d',
                    'accent_color' => '#28a745',
                    'registration_open' => true,
                ]);
            }
            
            return $settings;
        });
    }

    /**
     * Clear the cached settings.
     */
    public static function clearCache()
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Override save to clear cache.
     */
    public function save(array $options = [])
    {
        $result = parent::save($options);
        self::clearCache();
        return $result;
    }

    /**
     * Get logo URL.
     */
    public function getLogoUrlAttribute()
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }
        return asset('images/logo-default.png');
    }

    /**
     * Get favicon URL.
     */
    public function getFaviconUrlAttribute()
    {
        if ($this->favicon) {
            return asset('storage/' . $this->favicon);
        }
        return asset('favicon.ico');
    }

    /**
     * Get hero image URL.
     */
    public function getHeroImageUrlAttribute()
    {
        if ($this->hero_image) {
            return asset('storage/' . $this->hero_image);
        }
        return asset('images/hero-default.jpg');
    }

    /**
     * Get about image URL.
     */
    public function getAboutImageUrlAttribute()
    {
        if ($this->about_image) {
            return asset('storage/' . $this->about_image);
        }
        return null;
    }

    /**
     * Check if registration is currently open.
     */
    public function isRegistrationOpen()
    {
        if (!$this->registration_open) {
            return false;
        }

        $now = now();

        if ($this->registration_start && $now->lt($this->registration_start)) {
            return false;
        }

        if ($this->registration_end && $now->gt($this->registration_end)) {
            return false;
        }

        return true;
    }

    /**
     * Get WhatsApp link.
     */
    public function getWhatsappLinkAttribute()
    {
        if ($this->whatsapp) {
            $number = preg_replace('/[^0-9]/', '', $this->whatsapp);
            return 'https://wa.me/' . $number;
        }
        return null;
    }

    /**
     * Get full address for display.
     */
    public function getFullAddressAttribute()
    {
        return $this->address;
    }

    /**
     * Get Google Maps URL.
     */
    public function getMapsUrlAttribute()
    {
        if ($this->maps_latitude && $this->maps_longitude) {
            return "https://www.google.com/maps?q={$this->maps_latitude},{$this->maps_longitude}";
        }
        return null;
    }
}
