<?php

namespace App\Models;

use App\Enums\AnnouncementCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use HasFactory, MassPrunable;

    protected $fillable = [
        'unit_id',
        'created_by',
        'title',
        'content',
        'category',
        'is_global',
        'expires_at',
    ];

    protected $casts = [
        'category'   => AnnouncementCategory::class,
        'is_global'  => 'boolean',
        'expires_at' => 'datetime',
    ];

    /**
     * Auto-delete announcements that have passed their expiry date.
     */
    public function prunable()
    {
        return static::whereNotNull('expires_at')->where('expires_at', '<', now());
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
}
