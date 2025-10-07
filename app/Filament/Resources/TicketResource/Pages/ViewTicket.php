<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Infolists\Components\TextEntry;
use Filament\Actions;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    public function infolist(Infolists\Infolist $infolist): Infolists\Infolist
    {
        return $infolist
            ->schema([
                Tabs::make('Ticket Details')
                    ->tabs([
                        Tab::make('Detail')
                            ->schema([
                                TextEntry::make('ticket_number')->label('Ticket Number'),
                                TextEntry::make('user.name')->label('User'),
                                TextEntry::make('user.nik')->label('NIK'),
                                TextEntry::make('department.department_name')->label('Department'),
                                TextEntry::make('ticket_status')->badge(),
                                TextEntry::make('priority_level')->badge(),
                                TextEntry::make('category'),
                                TextEntry::make('type_device'),
                                TextEntry::make('operation_system'),
                                TextEntry::make('software_or_application'),
                                TextEntry::make('open_time')->label('Open Time')->badge(),
                                TextEntry::make('close_time')->label('Close Time')->badge(),
                                // TextEntry::make('attachment'),

                            ])
                            ->columns(2),

                        Tab::make('Follow Up')
                            ->schema([
                                TextEntry::make('description')
                                    ->label('Description')
                                    ->columnSpanFull(),
                                TextEntry::make('step_taken')
                                    ->label('Step Taken')
                                    ->html()
                                    ->columnSpanFull(),
                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->badge()
                                    ->getStateUsing(fn ($record) => $record->updated_at?->timezone('Asia/Jakarta')->format('d M Y H:i:s')),


                            ]),

                        Tab::make('History')
                            ->schema([
                                TextEntry::make('created_at')
                                    ->badge()
                                    ->label('Created At'),
                                TextEntry::make('updated_at')
                                    ->badge()
                                    ->label('Last Updated'),
                                TextEntry::make('close_time')
                                    ->badge()
                                    ->label('Closed At'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Back')
                ->icon('heroicon-o-arrow-left')
                ->url($this->getResource()::getUrl('index'))
                ->color('primary'),
        ];
    }
}
