<?php

namespace App\Filament\Imports;

use App\Models\Ticket;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Checkbox;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class TicketImporter extends Importer
{
    protected static ?string $model = Ticket::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('user_id')
                ->label('User NIK')
                ->requiredMapping()
                ->relationship(resolveUsing: 'nik')
                ->rules(['required']),
            ImportColumn::make('department_id')
                ->requiredMapping()
                ->relationship()
                ->rules(['required']),
            ImportColumn::make('ticket_number')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('open_time')
                ->requiredMapping()
                ->rules(['required', 'datetime']),
            ImportColumn::make('close_time')
                ->requiredMapping()
                ->rules(['required', 'datetime']),
            ImportColumn::make('category')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('description')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('type_device'),
            ImportColumn::make('operation_system'),
            ImportColumn::make('software_or_application')
                ->rules(['max:255']),
            ImportColumn::make('error_message'),
            ImportColumn::make('step_taken'),
            ImportColumn::make('ticket_status')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('priority_level'),
        ];
    }

    public function resolveRecord(): ?Ticket
    {
        // return Ticket::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Ticket();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your ticket import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
    public static function getOptionsFormComponents(): array
    {
        return [
            Checkbox::make('updateExisting')
                ->label('Update existing records'),
        ];
    }

    protected function afterImport(): void
    {
        Notification::make()
            ->title('Ticket import successfully')
            ->success()
            ->sendToDatabase(Auth::user());
    }
}
