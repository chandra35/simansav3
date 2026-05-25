<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Models\Download;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DownloadCategory extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected $table = 'download_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function downloads()
    {
        return $this->hasMany(Download::class, 'category_id');
    }
}
