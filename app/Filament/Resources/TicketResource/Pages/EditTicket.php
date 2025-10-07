<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as Notifications;
use Filament\Resources\Pages\EditRecord;

class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $ticket = $this->getRecord();

        $superAdmins = User::role('super_admin')->get();

        $currentUser = auth()->user();

        $users = $superAdmins->push($currentUser)->unique('id');

        Notifications::make()
            ->title('Ticket updated successfully')
            ->body("Tiket $ticket->ticket_number updated.")
            ->actions([
                    Action::make('view')
                        ->button()
                        ->url(TicketResource::getUrl('view', ['record' => $ticket]))
                        ->markAsRead(),
                ])
            ->success()
            ->send()
            ->sendToDatabase($users);
    }

    protected function getRedirectUrl(): string
    {
    return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?\Filament\Notifications\Notification
    {
    return null;
    }

}
