<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        return null;
    }

    protected function afterCreate(): void
    {
        // Get the newly created ticket record.
        $ticket = $this->getRecord();

        Notification::make()
                ->title('New ticket from ' . Auth::user()->name)
                ->body('Ticket Number: ' . $ticket->ticket_number)
                ->actions([
                    Action::make('view')
                        ->url(TicketResource::getUrl('index', ['record' => $ticket]))
                        ->markAsRead(),
                ])
                ->success()
                ->send()
                ->sendToDatabase(
                    User::role('super_admin')->get()
                );

    }
    protected function getRedirectUrl(): string
    {
    return TicketResource::getUrl('index');
    }

}
