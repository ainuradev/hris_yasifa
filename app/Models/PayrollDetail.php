<?php

namespace App\Models;

use App\Enums\PayrollCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollDetail extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'payroll_id',
        'description',
        'amount',
        'category',
    ];

    protected $casts = [
        'category' => PayrollCategory::class,
        'amount' => 'decimal:2',
    ];

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }
}
