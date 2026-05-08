<?php

namespace App\Observers;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditObserver
{
    public function created(Model $model): void
    {
        $this->logActivity($model, 'create', null, $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        
        // Hanya catat jika ada perubahan
        if (empty($changes)) return;

        $oldValues = [];
        $newValues = [];

        foreach ($changes as $key => $newValue) {
            // Abaikan kolom updated_at jika itu satu-satunya yang berubah
            if ($key === 'updated_at') continue;
            
            $oldValues[$key] = $model->getOriginal($key);
            $newValues[$key] = $newValue;
        }

        if (empty($oldValues)) return;

        $this->logActivity($model, 'update', $oldValues, $newValues);
    }

    public function deleted(Model $model): void
    {
        $this->logActivity($model, 'delete', $model->getAttributes(), null);
    }

    protected function logActivity(Model $model, string $action, ?array $old, ?array $new): void
    {
        // Hilangkan data sensitif jika ada
        $sensitive = ['password', 'remember_token'];
        if ($old) $old = array_diff_key($old, array_flip($sensitive));
        if ($new) $new = array_diff_key($new, array_flip($sensitive));

        ActivityLog::create([
            'user_id' => Auth::id(),
            'model_name' => get_class($model),
            'action' => $action,
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => request()->ip(),
        ]);
    }
}
