<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
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

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')
                    ->label('User')
                    ->default(Auth::id())
                    ->relationship('user', 'name')
                    ->reactive()
                    ->disabled(fn () => !auth()->user()->hasRole('super_admin'))
                    ->dehydrated(true)
                    ->required(),
                Forms\Components\TextInput::make('department_id')
                    ->label('Department')
                    ->options(fn () => Department::all()->pluck('name', 'id'))
                    ->default(fn () => auth()->user()->department_id)
                    ->disabled(fn () => !auth()->user()->hasRole('super_admin'))
                    ->dehydrated(true)
                    ->required(),
                Forms\Components\TextInput::make('ticket_number')
                    ->label('Ticket Number')
                    ->default(function () {
                    $yearSuffix = now()->format('y'); // Ambil 2 digit tahun, misalnya '25'
                    $countThisYear = \App\Models\Ticket::whereYear('created_at', now()->year)->count();
                    return 'TKC-' . $yearSuffix . str_pad($countThisYear + 1, 3, '0', STR_PAD_LEFT);
                    })

                    ->disabled()
                    ->dehydrated(false)
                    ->required(),
                // Forms\Components\DatePicker::make('date')
                //     ->required(),
                Forms\Components\TextInput::make('open_time')
                    ->required(),
                Forms\Components\TextInput::make('close_time')
                    ->required(),
                Forms\Components\TextInput::make('priority_level')
                    ->required(),
                Forms\Components\TextInput::make('category')
                    ->options([
                    'software' => 'Software',
                    'hardware' => 'Hardware',
                    'network' => 'Network',
                    'other' => 'Other',
                    ])
                    ->grouped()
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('type_device')
                    ->label('Device Type')
                    ->default(null)
                    ->options([
                        'desktop' => 'Desktop',
                        'laptop' => 'Laptop',
                        'printer' => 'Printer',
                     'other' => 'Other',
                    ])
                    ->visible(fn () => auth()->user()->hasRole('super_admin'))
                    ->grouped()
                    ->nullable(),
                Forms\Components\TextInput::make('operation_system')
                    ->default(null)
                    ->options([
                        'windows' => 'Windows',
                        'macos' => 'MacOS',
                        'linux' => 'Linux',
                        'other' => 'Other',
                    ])
                    ->visible(fn () => auth()->user()->hasRole('super_admin'))
                    ->grouped()
                    ->nullable(),
                Forms\Components\TextInput::make('software_or_application')
                    ->visible(fn () => auth()->user()->hasRole('super_admin'))
                    ->default(null)
                    ->dehydrated(true),
                Forms\Components\Textarea::make('error_message')
                    ->visible(fn () => auth()->user()->hasRole('super_admin'))
                    ->columnSpanFull()
                    ->nullable(),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull()
                    ->required(),
                Forms\Components\Textarea::make('step_taken')
                    ->disabled(fn () => !auth()->user()->hasRole('super_admin'))
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\ToggleButtons::make('priority_level')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                    ])
                    ->default('low')
                    ->grouped()
                    ->nullable()
                    ->disabled(fn () => !Auth::user()?->hasRole('super_admin')),
                Forms\Components\TextInput::make('ticket_status')
                    ->label('Status')
                    ->options([
                        'on_progress' => 'On Progress',
                        'solved' => 'Solved',
                        'callback' => 'Callback',
                        'monitored' => 'Monitored',
                        'other' => 'Other',
                 ])
                    ->default('on_progress')
                    ->disabled(fn () => !Auth::user()?->hasRole('super_admin'))
                        ->nullable(),
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
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ticket_number')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('date')
                //     ->date()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('open_time')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('close_time')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('priority_level'),
                Tables\Columns\TextColumn::make('category'),
                Tables\Columns\TextColumn::make('type_device'),
                Tables\Columns\TextColumn::make('operation_system'),
                Tables\Columns\TextColumn::make('software_or_application')
                    ->searchable(),
                Tables\Columns\TextColumn::make('priority_level'),
                Tables\Columns\TextColumn::make('ticket_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'other' => 'primary',
                        'on_progress' => 'info',
                        'solved' => 'success',
                        'monitored' => 'warning',
                        'callback' => 'danger',
                    }),
                 Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('ticket_status')
                        ->label('Ticket Status')
                        ->multiple()
                        ->options([
                            'on_progress' => 'On Progress',
                            'solved' => 'Solved',
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
                                'solved' => 'Solved',
                                'callback' => 'Callback',
                                'monitored' => 'Monitored',
                                'other' => 'Other',
                            ])
                            ->required(),
                        RichEditor::make('content')->label('Note'),
                    ])
                    ->modalHeading('Update Ticket Status')
                    ->icon('heroicon-m-arrow-path')
                    ->visible(fn () => auth()->user()->hasRole('super_admin')),
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
            'index' => Pages\ManageTickets::route('/'),
        ];
    }
}
