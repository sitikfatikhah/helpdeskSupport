<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Filament\Resources\ActivityLogResource\RelationManagers;
use App\Models\ActivityLog;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ActivityLogResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = ActivityLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationGroup = 'Setting';

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'publish'
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            TextInput::make('model_type')
                ->label('Model')
                ->disabled(),

            TextInput::make('model_id')
                ->label('Model ID')
                ->disabled(),

            TextInput::make('action')
                ->label('Action')
                ->disabled(),

            TextInput::make('causer.name')
                ->label('User')
                ->default('System')
                ->disabled(),

            Textarea::make('changes')
                ->label('Changes')
                ->formatStateUsing(function ($state) {
                    $decoded = json_decode($state, true);
                    if (!$decoded || !is_array($decoded)) {
                        return '-';
                    }

                    return collect($decoded)
                        ->map(fn($value, $key) => "{$key} → {$value}")
                        ->implode(', ');
                })
                ->disabled(),

            TextInput::make('created_at')
                ->label('Logged At')
                ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->format('d M Y H:i'))
                ->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('model_type')
                    ->label('Model')
                    ->searchable(),

                Tables\Columns\TextColumn::make('model_id')
                    ->label('Model ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('action')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('causer.name')
                    ->label('User')
                    ->searchable()
                    ->default('System'),

                Tables\Columns\TextColumn::make('changes')
                    ->label('Changes')
                    ->formatStateUsing(fn ($state) => json_encode($state))
                    ->formatStateUsing(function ($state) {
                    $decoded = json_decode($state, true);
                    if (!$decoded || !is_array($decoded)) {
                     return '-';
                    }
                    return collect($decoded)
                        ->map(fn($value, $key) => "{$key} → {$value}")
                        ->implode(', ');
                    })
                    ->limit(50),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->sortable()
                    ->dateTime('d M Y H:i'),
            ])

            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
            'create' => Pages\CreateActivityLog::route('/create'),
            'edit' => Pages\ViewActivityLog::route('/{record}/edit'),
        ];
    }

}
