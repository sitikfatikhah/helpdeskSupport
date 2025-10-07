<?php

namespace App\Observers;

use App\Models\Company;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class CompanyObserver
{
    public function created(Company $company): void
    {
        $this->logActivity($company, 'created');
    }

    public function updated(Company $company): void
    {
        $changes = $company->getChanges();

        // Biasanya kita abaikan perubahan timestamp updated_at
        unset($changes['updated_at']);

        if (!empty($changes)) {
            $this->logActivity($company, 'updated', $changes);
        }
    }

    public function deleted(Company $company): void
    {
        $this->logActivity($company, 'deleted');
    }

    public function restored(Company $company): void
    {
        $this->logActivity($company, 'restored');
    }

    public function forceDeleted(Company $company): void
    {
        $this->logActivity($company, 'force_deleted');
    }

    /**
     * Fungsi untuk mencatat log aktivitas ke tabel activity_logs
     *
     * @param Company $model
     * @param string $action
     * @param array|null $changes
     */
    protected function logActivity(Company $model, string $action, array $changes = null): void
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
