<?php

namespace App\Observers;

use App\Models\Department;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class DepartmentObserver
{
    public function created(Department $department): void
    {
        $this->logActivity($department, 'created');
    }

    public function updated(Department $department): void
    {
        $changes = $department->getChanges();

        // Abaikan perubahan timestamp updated_at agar log lebih bersih
        unset($changes['updated_at']);

        if (!empty($changes)) {
            $this->logActivity($department, 'updated', $changes);
        }
    }

    public function deleted(Department $department): void
    {
        $this->logActivity($department, 'deleted');
    }

    public function restored(Department $department): void
    {
        $this->logActivity($department, 'restored');
    }

    public function forceDeleted(Department $department): void
    {
        $this->logActivity($department, 'force_deleted');
    }

    /**
     * Fungsi untuk mencatat log aktivitas ke tabel activity_logs
     *
     * @param Department $model
     * @param string $action
     * @param array|null $changes
     */
    protected function logActivity(Department $model, string $action, array $changes = null): void
    {
        ActivityLog::create([
            'model_type' => get_class($model),
            'model_id'   => $model->getKey(),
            'action'     => $action,
            'changes'    => $changes ? json_encode($changes) : null,
            'causer_id'  => Auth::check() ? Auth::id() : null,
        ]);
    }
}
