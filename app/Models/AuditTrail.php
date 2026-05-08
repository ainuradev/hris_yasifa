<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditTrail extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'actor_employee_id',
        'action',
        'auditable_type',
        'auditable_id',
        'description',
        'before_data',
        'after_data',
        'created_at',
    ];

    protected $casts = [
        'before_data' => 'array',
        'after_data' => 'array',
        'created_at' => 'datetime',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'actor_employee_id');
    }
}
