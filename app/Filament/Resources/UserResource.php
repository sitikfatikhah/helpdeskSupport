<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Actions\ImportAction;
use App\Models\Company;
use App\Models\Department;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Illuminate\Database\Eloquent\Model;

class UserResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = User::class;

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationIcon = 'heroicon-s-users';

    protected static ?string $navigationGroup = 'Master User';

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
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('nik')
                    ->label('NIK')
                    ->required()
                    ->maxLength(6)
                    ->unique(table: User::class, column: 'nik', ignoreRecord: true),
                Forms\Components\Select::make('role')
                    ->relationship('roles', 'name')
                    ->preload()
                    ->searchable()
                    ->label('Roles'),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\Select::make('company_id')
                    ->relationship('company', 'company_name')
                    ->label('Company')
                    ->preload()
                    ->searchable()
                    ->required()
                    ->validationMessages([
                        'required' => 'Silakan pilih perusahaan terlebih dahulu.',
                        'exists'   => 'Perusahaan yang dipilih tidak ditemukan.',
                    ]),

                Forms\Components\Select::make('department_name')
                    ->relationship('department', 'department_name')
                    ->label('Department')
                    ->preload()
                    ->searchable()
                    ->required()
                    ->validationMessages([
                        'required' => 'Silakan pilih departemen terlebih dahulu.',
                        'exists'   => 'Departemen yang dipilih tidak ditemukan.',
                    ]),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])
                    ->required(),
                // Forms\Components\Select::make('role')
                //     ->relationship('roles', 'name')
                //     ->preload()
                //     ->searchable()
                //     ->label('Roles'),
                // Forms\Components\DateTimePicker::make('email_verified_at'),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->maxLength(255)
                    ->revealable()
                    ->required()
                    // ->dehydrateStateUsing(fn ($state) => ! empty($state) ? bcrypt($state) : null)
                    // ->dehydrated(fn ($state) => filled($state))
                    ->label('Password'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->searchable(),
                Tables\Columns\TextColumn::make('nik')->label('NIK')->searchable()->formatStateUsing(fn ($state) => ucfirst(strtolower($state))),
                Tables\Columns\TextColumn::make('name')->searchable()->formatStateUsing(fn ($state) => ucfirst(strtolower($state))),
                Tables\Columns\TextColumn::make('roles.name')->label('Roles')->badge()->sortable()->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('company.company_name')->label('Company')->searchable(),
                Tables\Columns\TextColumn::make('department.department_name')->label('Department')->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => ucfirst(strtolower($state)))
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'active' => 'primary',
                        'inactive' => 'gray',
                        default => 'secondary',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('updateStatus')
                    ->label('Update Status')
                    ->form([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])
                            ->required(),
                        RichEditor::make('content'),
                    ])
                    ->action(function (array $data, User $record): void {
                        $record->status = $data['status'];
                        $record->save();
                    })
                    ->modalHeading('Update User Status')
                    ->icon('heroicon-m-arrow-path'),

                Tables\Actions\EditAction::make()
                 ->using(function (Model $record, array $data): Model {
                        $record->update($data);

                        return $record;
                        // dd($data);
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUsers::route('/'),
        ];
    }

}
