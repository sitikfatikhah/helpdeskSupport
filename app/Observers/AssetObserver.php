<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Asset;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Auth;

class AssetObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the Asset "created" event.
     */
    public function created(Asset $asset): void
    {
        $this->logActivity($asset, 'created', [
            'new' => $asset->getAttributes(),
        ]);
    }

    /**
     * Handle the Asset "updated" event.
     */
    public function updated(Asset $asset): void
    {
        $changes = $asset->getChanges();

        // Biasanya kita abaikan perubahan timestamp updated_at
        unset($changes['updated_at']);

        if (!empty($changes)) {
            $this->logActivity($asset, 'updated', $changes);
        }
    }

    /**
     * Handle the Asset "deleted" event.
     */
    public function deleted(Asset $asset): void
    {
        $this->logActivity($asset, 'deleted', [
            'old' => $asset->getOriginal(),
        ]);
    }

    /**
     * Handle the Asset "restored" event.
     */
    public function restored(Asset $asset): void
    {
        $this->logActivity($asset, 'restored');
    }

    /**
     * Handle the Asset "force deleted" event.
     */
    public function forceDeleted(Asset $asset): void
    {
        $this->logActivity($asset, 'force_deleted');
    }

    /**
     * Fungsi untuk mencatat log aktivitas ke tabel activity_logs
     *
     * @param Company $model
     * @param string $action
     * @param array|null $changes
     */
    protected function logActivity(Asset $model, string $action, array $changes = null): void
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
