<?php

namespace App\Services;

use App\Models\AuditTrail;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;

class AuditTrailService
{
    public function record(
        ?Employee $actor,
        string $action,
        Model|string $auditable,
        ?string $description = null,
        ?array $before = null,
        ?array $after = null
    ): void {
        AuditTrail::create([
            'actor_employee_id' => $actor?->id,
            'action' => $action,
            'auditable_type' => $auditable instanceof Model ? $auditable::class : $auditable,
            'auditable_id' => $auditable instanceof Model ? $auditable->getKey() : null,
            'description' => $description,
            'before_data' => $before,
            'after_data' => $after,
            'created_at' => now(),
        ]);
    }
}
