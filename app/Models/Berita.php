<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Berita extends Model
{
    use HasFactory;

    /**
     * Indicates if the model's ID is auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'judul',
        'slug',
        'konten',
        'excerpt',
        'gambar',
        'kategori',
        'penulis',
        'views',
        'is_featured',
        'status',
        'shared_to_facebook',
        'facebook_post_id',
        'published_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'views' => 'integer',
        'is_featured' => 'boolean',
        'shared_to_facebook' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * Bootstrap the model and its traits.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }

            // Auto-generate slug from judul
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->judul);
            }

            // Auto-generate excerpt from konten
            if (empty($model->excerpt) && !empty($model->konten)) {
                $model->excerpt = Str::limit(strip_tags($model->konten), 200);
            }
        });

        static::updating(function ($model) {
            // Auto-update slug if judul changed
            if ($model->isDirty('judul')) {
                $model->slug = Str::slug($model->judul);
            }

            // Auto-update excerpt if konten changed
            if ($model->isDirty('konten') && empty($model->excerpt)) {
                $model->excerpt = Str::limit(strip_tags($model->konten), 200);
            }
        });
    }

    /**
     * Scope a query to only include active berita.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include featured berita.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to filter by kategori.
     */
    public function scopeByKategori($query, $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    /**
     * Scope a query to only include published berita.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'active')
                     ->where(function ($q) {
                         $q->whereNull('published_at')
                           ->orWhere('published_at', '<=', now());
                     });
    }

    /**
     * Get gambar URL.
     */
    public function getGambarUrlAttribute()
    {
        if ($this->gambar) {
            return asset('storage/' . $this->gambar);
        }
        return asset('images/placeholder-berita.jpg');
    }

    /**
     * Get berita URL.
     */
    public function getUrlAttribute()
    {
        return route('ppdb.berita.detail', $this->slug);
    }

    /**
     * Increment view count.
     */
    public function incrementViews()
    {
        $this->increment('views');
    }

    /**
     * Get formatted date.
     */
    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('d M Y');
    }

    /**
     * Get relative date (time ago).
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}
