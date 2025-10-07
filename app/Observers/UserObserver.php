<?php

namespace App\Observers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $this->logActivity($user, 'created');
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $changes = $user->getChanges();

        // Hapus updated_at agar tidak ikut tercatat
        unset($changes['updated_at']);

        if (!empty($changes)) {
            $this->logActivity($user, 'updated', $changes);
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        $this->logActivity($user, 'deleted');
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        $this->logActivity($user, 'restored');
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        $this->logActivity($user, 'force_deleted');
    }

    /**
     * Fungsi untuk mencatat log aktivitas ke tabel activity_logs.
     *
     * @param User   $model   Model yang mengalami perubahan (User).
     * @param string $action  Jenis aksi yang dilakukan (created, updated, deleted, dll).
     * @param array|null $changes  Data yang berubah (jika ada).
     */
    protected function logActivity(User $model, string $action, array $changes = null): void
    {
        ActivityLog::create([
            'model_type' => get_class($model),
            'model_id'   => $model->getKey(),
            'action'     => $action,
            'changes'    => $changes ? json_encode($changes) : null, // Perubahan data (jika ada)
            'causer_id'  => Auth::check() ? Auth::id() : null,
        ]);
    }
}
