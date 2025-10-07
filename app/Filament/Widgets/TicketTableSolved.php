<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class TicketTableSolved extends BaseWidget
{
    use HasWidgetShield;

    protected static ?string $maxHeight = '400px';

    protected static ?string $heading = 'Ticket Table Resolved';

    public function table(Table $table): Table
    {
        $user = Auth::user();

        $query = Ticket::query()
            ->where('ticket_status', 'resolved');

        // 🔑 Filter berdasarkan user login jika bukan super_admin
        if (!$user->hasRole('super_admin')) {
            $query->where('user_id', $user->id);
        }

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('ticket_number')->label('Ticket #'),
                TextColumn::make('open_time')->label('Opened At'),
                TextColumn::make('close_time')->label('Closed At'),
                TextColumn::make('description')->wrap()->lineClamp(3),
                TextColumn::make('ticket_status')->label('Status'),
            ]);
    }
}
