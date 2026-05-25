<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Models\DownloadCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Download extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected $table = 'downloads';

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'description',
        'source',
        'file_name_original',
        'file_extension',
        'mime_type',
        'file_size',
        'local_disk',
        'local_path',
        'gdrive_file_id',
        'gdrive_file_url',
        'is_published',
        'is_featured',
        'published_at',
        'download_count',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
        'file_size' => 'integer',
        'download_count' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(DownloadCategory::class, 'category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = (int) floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);
        $value = $bytes / (1024 ** $power);

        return number_format($value, $power === 0 ? 0 : 1, ',', '.') . ' ' . $units[$power];
    }

    public function getDownloadRouteFilenameAttribute(): string
    {
        $baseName = pathinfo($this->file_name_original ?: $this->slug, PATHINFO_FILENAME);
        $safeBaseName = Str::slug($baseName ?: $this->slug ?: 'download-file');
        $extension = strtolower((string) $this->file_extension);

        return $extension !== ''
            ? $safeBaseName . '.' . $extension
            : $safeBaseName;
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }
}
