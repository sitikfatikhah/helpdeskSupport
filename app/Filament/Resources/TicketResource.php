<?php

namespace App\Filament\Resources;

use App\Filament\Exports\TicketExporter;
use App\Filament\Imports\TicketImporter;
use App\Filament\Resources\TicketResource\Pages;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Tables\Actions\ImportAction;
use Filament\Forms;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Panel;
use Filament\Tables\Actions\ExportAction;
use Filament\Support\Enums\Width;

class TicketResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Ticket::class;

    protected static ?int $navigationSort = 2;


    protected static ?string $navigationIcon = 'heroicon-o-ticket';

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
        Forms\Components\Select::make('user_id')
            ->label('User')
            ->default(Auth::id())
            ->relationship('user', 'name')
            ->reactive()
            ->disabled(fn () => !auth()->user()->hasRole('super_admin'))
            ->dehydrated(true)
            ->required()
            ->live(onBlur: true)
            ->helperText('Select ticket owner.'),

        Forms\Components\Select::make('nik')
            ->label('NIK')
            ->default(Auth::id())
            ->relationship('user', 'nik')
            ->reactive()
            ->disabled(fn () => !auth()->user()->hasRole('super_admin'))
            ->dehydrated(true)
            ->required()
            ->helperText('Employee ID number.'),

        Forms\Components\Select::make('department_id')
            ->label('Department')
            ->relationship('department', 'department_name')
            ->default(fn () => auth()->user()->department_id)
            ->disabled(fn () => !auth()->user()->hasRole('super_admin'))
            ->dehydrated(true)
            ->required()
            ->helperText('Choose department.'),


        Forms\Components\TextInput::make('ticket_number')
            ->label('Ticket Number')
            ->default(function () {
                $yearSuffix = now()->format('y');
                $countThisYear = \App\Models\Ticket::whereYear('created_at', now()->year)->count();
                return 'TKC-' . $yearSuffix . str_pad($countThisYear + 1, 3, '0', STR_PAD_LEFT);
            })
            ->disabled(fn () => !auth()->user()->hasRole('super_admin'))
            ->dehydrated(false)
            ->required()
            ->helperText('Auto-generated ticket ID.'),

        Forms\Components\DateTimePicker::make('open_time')
            ->default(now()->format('Y-m-d H:i'))
            ->disabled(fn () => !Auth::user()?->hasRole('super_admin'))
            ->required()
            ->dehydrated(true)
            ->helperText('Ticket start time.'),

        Forms\Components\DateTimePicker::make('close_time')
            ->disabled(fn () => !Auth::user()?->hasRole('super_admin'))
            ->nullable()
            ->dehydrated(true)
            ->helperText('Ticket close time.'),

        Forms\Components\ToggleButtons::make('category')
            ->options([
                'software' => 'Software',
                'hardware' => 'Hardware',
                'network' => 'Network',
                'other' => 'Other',
            ])
            ->required()
            ->inline()
            ->grouped()
            ->helperText('Select issue type.'),

        Forms\Components\Select::make('type_device')
            ->label('Device Type')
            ->default(null)
            ->options([
                'desktop' => 'Desktop',
                'laptop' => 'Laptop',
                'printer' => 'Printer',
                'other' => 'Other',
            ])
            ->visible(fn () => auth()->user()->hasRole('super_admin'))
            ->nullable()
            ->helperText('Choose device type.'),

        Forms\Components\Select::make('operation_system')
            ->default(null)
            ->options([
                'windows' => 'Windows',
                'macos' => 'MacOS',
                'linux' => 'Linux',
                'other' => 'Other',
            ])
            ->visible(fn () => auth()->user()->hasRole('super_admin'))
            ->nullable()
            ->helperText('Select OS type.'),

        Forms\Components\TextInput::make('software_or_application')
            ->visible(fn () => auth()->user()->hasRole('super_admin'))
            ->default(null)
            ->dehydrated(true)
            ->helperText('Name of software.'),

        Forms\Components\Select::make('ticket_status')
            ->label('Status')
            ->options([
                'on_progress' => 'On Progress',
                'resolved' => 'Resolved',
                'callback' => 'Callback',
                'monitored' => 'Monitored',
                'other' => 'Other',
            ])
            ->default('on_progress')
            ->disabled(fn () => !auth()->user()->hasRole('super_admin'))
            ->nullable()
            ->helperText('Select status.'),

        Forms\Components\Textarea::make('error_message')
            ->visible(fn () => auth()->user()->hasRole('super_admin'))
            ->autosize()
            ->nullable()
            ->helperText('Write error message.'),

        Forms\Components\Textarea::make('description')
            ->autosize()
            ->required()
            ->helperText('Describe the issue.'),

        Forms\Components\Textarea::make('step_taken')
            ->afterStateUpdated(function ($state, $record) {
        if ($record && $record->user) {
            $record->user->touch();
        }
            })
            ->hidden(fn () => !auth()->user()->hasRole('super_admin'))
            ->autosize()
            ->helperText('Steps already taken.'),

        // Forms\Components\FileUpload::make('attachment')
        //     ->label('Attachments')
        //     ->disk('public')
        //     ->directory('form-attachments')
        //     ->visibility('public')
        //     ->multiple()
        //     ->downloadable()
        //     ->storeFileNamesIn('attachment_file_names')
        //     ->preserveFilenames()
        //     ->helperText('Upload related files.'),

        Forms\Components\ToggleButtons::make('priority_level')
            ->options([
                'low' => 'Low',
                'medium' => 'Medium',
                'high' => 'High',
            ])
            ->default('low')
            ->grouped()
            ->nullable()
            ->disabled(fn () => !Auth::user()?->hasRole('super_admin'))
            ->helperText('Set ticket priority.'),
    ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                // Mendapatkan user yang sedang login
                $user = Auth::user();

                // Jika user BUKAN super_admin, filter tiket sesuai user_id yang login
                if (!$user->hasRole('super_admin')) {
                    $query->where('user_id', $user->id);
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.nik')
                    ->label('NIK')
                    ->formatStateUsing(fn ($state) => (string) $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('department.department_name')
                    ->label('Department')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ticket_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('open_time')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('close_time')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('category')->formatStateUsing(fn ($state) => ucfirst($state)),
                Tables\Columns\TextColumn::make('type_device')->formatStateUsing(fn ($state) => ucfirst($state)),
                Tables\Columns\TextColumn::make('operation_system')->formatStateUsing(fn ($state) => ucfirst($state)),
                // Tables\Columns\TextColumn::make('description')->formatStateUsing(fn ($state) => ucfirst($state)),
                // Tables\Columns\TextColumn::make('step_taken')->label('Step taken')->html(false)->formatStateUsing(fn ($state) => ucfirst(strip_tags($state))),
                // Tables\Columns\ImageColumn::make('attachment'),
                Tables\Columns\TextColumn::make('software_or_application')->formatStateUsing(fn ($state) => ucfirst($state))->searchable(),
                Tables\Columns\TextColumn::make('priority_level')->formatStateUsing(fn ($state) => ucfirst($state)),
                Tables\Columns\TextColumn::make('ticket_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'other' => 'primary',
                        'on_progress' => 'info',
                        'resolved' => 'success',
                        'monitored' => 'warning',
                        'callback' => 'danger',
                    }),
                // Tables\Columns\TextColumn::make('created_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                // Tables\Columns\TextColumn::make('updated_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('ticket_status')
                        ->label('Ticket Status')
                        ->multiple()
                        ->options([
                            'on_progress' => 'On Progress',
                            'resolved' => 'Resolved',
                            'callback' => 'Callback',
                            'monitored' => 'Monitored',
                            'other' => 'Other',
                        ]),

                    SelectFilter::make('user_id')
                        ->label('User')
                        ->relationship('user', 'name', fn ($query) => $query->orderBy('name'))
                        ->searchable()
                        ->preload(),

                    SelectFilter::make('category')
                        ->options([
                            'software' => 'Software',
                            'hardware' => 'Hardware',
                            'network' => 'Network',
                            'other' => 'Other',
                        ]),
                    SelectFilter::make('type_device')
                        ->label('Device Type')
                        ->options([
                            'desktop' => 'Desktop',
                            'laptop' => 'Laptop',
                            'printer' => 'Printer',
                            'other' => 'Other',
                        ])
                        ],
                    layout: FiltersLayout::AboveContent
            )
            ->actions([
                Action::make('updateStatus')
                    ->label('Update Status')
                    ->form([
                        Select::make('ticket_status')
                            ->label('Status')
                            ->options([
                                'on_progress' => 'On Progress',
                                'resolved' => 'Resolved',
                                'callback' => 'Callback',
                                'monitored' => 'Monitored',
                                'other' => 'Other',
                            ])
                            ->required(),
                        RichEditor::make('content')->label('Step Taken')->default(fn ($record) => $record->step_taken),
                    ])
                    ->modalHeading('Update Ticket Status')
                    ->icon('heroicon-m-arrow-path')
                    ->visible(fn () => Auth::check() && Auth::user()->hasRole('super_admin'))
                    ->action(function(array $data, $record) {
                        $record->update([
                            'ticket_status' => $data['ticket_status'],
                            'step_taken' => $data['content'] ?? $record->step_taken,
                        ]);
                    }),

                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),

            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(TicketImporter::class),
                ExportAction::make()
                    ->exporter(TicketExporter::class)
                ]);
    }
    public static function getPages(): array
    {
        return [
            'create' => Pages\CreateTicket::route('/create'),
            'index' => Pages\ManageTickets::route('/'),
            'view' => Pages\ViewTicket::route('/{record}'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
    public static function panel(Panel $panel): Panel
    {
    return $panel
        ->label('Tickets')
        // ->navigationSort(1)
        ->maxContentWidth('full'); // pakai string
    }

    protected function getRedirectUrl(): string
    {
    return TicketResource::getUrl('index');
    }


}
