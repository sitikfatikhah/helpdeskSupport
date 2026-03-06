<?php

namespace App\Filament\Imports;

use App\Models\Asset;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Checkbox;

class AssetImporter extends Importer
{
    protected static ?string $model = Asset::class;

    // protected static bool $shouldSkipColumnMapping = true;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('device_id')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('type')
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('status')
                ->rules(['nullable', 'string', 'max:255']),

            // owner di CSV → user_id di database
            ImportColumn::make('user_id')
                ->label('owner')
                ->rules(['nullable', 'exists:users,id']),

            // location di CSV → department_id di database
            ImportColumn::make('department_id')
                ->label('location')
                ->rules(['nullable', 'exists:departments,id']),

            ImportColumn::make('inventory_id')
                ->rules(['nullable', 'string', 'max:255']),

            ImportColumn::make('ram')
                ->rules(['nullable', 'string', 'max:255']),

            ImportColumn::make('serial_number')
                ->rules(['nullable', 'string', 'max:255']),

            ImportColumn::make('os_name')
                ->rules(['nullable', 'json', 'max:255']),

            ImportColumn::make('info')
                ->rules(['nullable', 'text', 'max:255']),

            ImportColumn::make('brand')
                ->rules(['nullable', 'string', 'max:255']),
        ];
    }

    public function resolveRecord(): ?Asset
    {
        try {
            return Asset::firstOrNew([
                'email' => $this->data['email'],
            ]);
        } catch (\Throwable $e) {
            $this->addFailure(
                'general',
                $e->getMessage(),
            );

            return null;
        }
    }


    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your asset import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

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
    public function beforeSave(array $data): array
    {
        logger()->info('Import row', $data);

        return $data;
    }
}
