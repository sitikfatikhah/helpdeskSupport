<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetResource\Pages;
use App\Models\Asset;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\ExportAction;
use App\Filament\Exports\AssetExporter;
use App\Filament\Imports\AssetImporter;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Get;
use Filament\Tables\Columns\IconColumn;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ImportAction;

class AssetResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Asset::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

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
        return $form->schema([
            Tabs::make('Asset Tabs')
                ->columnSpanFull()
                ->tabs([

                    Tab::make('General')
                        ->schema([
                            TextInput::make('device_id')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->label('Device ID')
                                ->helperText('Kode unik perangkat'),

                            Select::make('type')
                                ->required()
                                ->options([
                                    'Laptop' => 'Laptop',
                                    'PC' => 'PC',
                                    'Printer' => 'Printer',
                                    'Keyboard' => 'Keyboard',
                                    'Mouse' => 'Mouse',
                                    'CPU' => 'CPU',
                                    'Other' => 'Other',
                                ])
                                ->label('Type'),

                            Select::make('status')
                                ->required()
                                ->options([
                                    'available' => 'Available',
                                    'assign' => 'Assigned',
                                    'maintenance' => 'Maintenance',
                                    'retired' => 'Retired',
                                ])
                                ->default('available')
                                ->reactive()
                                ->label('Status'),

                            Select::make('user_id')
                                ->relationship('user', 'nik')
                                ->getOptionLabelFromRecordUsing(
                                    fn($record) => "{$record->nik} - {$record->name}"
                                )
                                ->searchable(['nik', 'name'])
                                ->requiredIf('status', 'assign')
                                ->disabled(fn(Get $get) => $get('status') !== 'assign')
                                ->dehydrated(fn(Get $get) => $get('status') === 'assign')
                                ->preload()
                                ->label('Owner'),

                            Select::make('department_id')
                                ->relationship('department', 'department_name')
                                ->searchable()
                                ->requiredIf('status', 'assign')
                                ->disabled(fn(Get $get) => $get('status') !== 'assign')
                                ->dehydrated(fn(Get $get) => $get('status') === 'assign')
                                ->preload()
                                ->label('Location'),

                            TextInput::make('inventory_id')
                                ->label('Inventory ID'),
                        ])
                        ->columns(2),

                    Tab::make('Specification')
                        ->schema([
                            TextInput::make('ram')
                                ->label('RAM')
                                ->placeholder('Contoh: 8 GB'),

                            TextInput::make('serial_number')
                                ->label('Serial Number'),

                            Select::make('os_name')
                                ->options([
                                    'Windows' => 'Windows',
                                    'Macos' => 'MacOS',
                                    'Linux' => 'Linux',
                                    'other' => 'Other',
                                ])
                                ->multiple()
                                ->label('Operating System'),
                            TextInput::make('brand')
                                ->label('Brand')
                                ->placeholder('Contoh: Dell, HP, Asus, dll')
                        ])

                        ->columns(2),

                    Tab::make('Description')
                        ->schema([
                            Textarea::make('info')
                                ->label('Information')
                                ->placeholder('Contoh: Intel i5, 256GB SSD, dll')
                                ->rows(5)
                                ->columnSpanFull()
                        ])
                        ->columns(3),
                ]),
        ]);
    }




    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('device_id')->label('Device ID')->sortable()->searchable(),
                IconColumn::make('type')
                    ->label('Type')
                    ->icon(fn(string $state) => match ($state) {
                        'Laptop'  => 'ik-laptop',
                        'PC'      => 'heroicon-o-computer-desktop',
                        'Printer' => 'heroicon-o-printer',
                        'Keyboard' => 'bi-keyboard',
                        'Mouse' => 'bi-mouse',
                        'CPU' => 'phosphor-computer-tower-thin',
                        default   => 'heroicon-o-cube',
                    })
                    ->tooltip(fn($state) => $state),
                TextColumn::make('status')->label('Status')->sortable()->badge(),
                TextColumn::make('user.nik')->label('Owner')
                    ->formatStateUsing(
                        fn($state, $record) =>
                        $record->user
                            ? "{$record->user->nik} - {$record->user->name}"
                            : '-'
                    )
                    ->sortable()
                    ->searchable(),
                TextColumn::make('department.department_name')->label('Location')->sortable()->searchable(),
                TextColumn::make('inventory_id')->label('Inventory ID')->sortable()->searchable(),
                // TextColumn::make('info')->label('Specification')->limit(30),
                TextColumn::make('os_name')->label('Operating System')->sortable()->searchable(),
                TextColumn::make('brand')->label('Brand')->sortable()->searchable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'available' => 'Available',
                        'assign' => 'Assigned',
                        'maintenance' => 'Under Maintenance',
                        'retired' => 'Retired',
                    ]),
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(AssetImporter::class),
                ExportAction::make()
                    ->exporter(AssetExporter::class),
            ])


            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'create' => Pages\CreateAsset::route('/create'),
            'index' => Pages\ListAssets::route('/'),
            'edit' => Pages\EditAsset::route('/{record}/edit'),
            'view' => Pages\ViewAsset::route('/{record}/view'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return TicketResource::getUrl('index');
    }
}
