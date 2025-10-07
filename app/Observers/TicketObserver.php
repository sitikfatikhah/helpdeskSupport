<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class TicketObserver
{

    public function created(Ticket $ticket): void
    {
        $this->logActivity($ticket, 'created');
    }


    public function updated(Ticket $ticket): void
    {
        $changes = $ticket->getChanges();

        // Hilangkan perubahan updated_at jika tidak penting
        unset($changes['updated_at']);

        if (!empty($changes)) {
            $this->logActivity($ticket, 'updated', $changes);
        }
    }


    public function deleted(Ticket $ticket): void
    {
        $this->logActivity($ticket, 'deleted');
    }


    public function restored(Ticket $ticket): void
    {
        $this->logActivity($ticket, 'restored');
    }


    public function forceDeleted(Ticket $ticket): void
    {
        $this->logActivity($ticket, 'force_deleted');
    }

    /**
     * Fungsi untuk mencatat log aktivitas.
     *
     * @param Ticket $model Model yang sedang diproses (Ticket).
     * @param string $action Jenis aksi (created, updated, etc).
     * @param array|null $changes Perubahan data jika ada.
     */
    protected function logActivity(Ticket $model, string $action, array $changes = null): void
    {
        ActivityLog::create([
            'model_type' => get_class($model),
            'model_id'   => $model->getKey(),
            'action'     => $action,
            'changes'    => $changes ? json_encode($changes) : null, // Perubahan (jika ada)
            'causer_id'  => Auth::check() ? Auth::id() : null,
        ]);
    }
}
