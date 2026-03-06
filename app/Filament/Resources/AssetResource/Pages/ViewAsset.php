<?php

namespace App\Filament\Resources\AssetResource\Pages;

use App\Filament\Resources\AssetResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Infolists\Components\TextEntry;
use Filament\Actions;

class ViewAsset extends ViewRecord
{
    protected static string $resource = AssetResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Tabs::make('Asset Details')
                ->tabs([

                    // =====================
                    // DETAIL TAB
                    // =====================
                    Tab::make('Detail')
                        ->schema([
                            TextEntry::make('device_id')->label('Device ID'),
                            TextEntry::make('type')->label('Type'),
                            TextEntry::make('status')->label('Status')->badge(),

                            TextEntry::make('user.nik')
                                ->label('Owner NIK')
                                ->placeholder('-'),

                            TextEntry::make('user.name')
                                ->label('Owner Name')
                                ->placeholder('-'),

                            TextEntry::make('department.department_name')
                                ->label('Location')
                                ->placeholder('-'),

                            TextEntry::make('inventory_id')
                                ->label('Inventory ID')
                                ->placeholder('-'),
                            TextEntry::make('brand')
                                ->label('Brand')
                                ->placeholder('-'),
                        ])
                        ->columns(3),

                    // =====================
                    // INFO TAB
                    // =====================
                    Tab::make('Device Info')
                        ->schema([
                            TextEntry::make('ram')
                                ->label('RAM')
                                ->placeholder('-'),
                            TextEntry::make('serial_number')
                                ->label('Serial Number')
                                ->placeholder('-'),
                            TextEntry::make('os_name')
                                ->label('Operating System')
                                ->placeholder('-'),
                            TextEntry::make('info')
                                ->label('Info')
                                ->placeholder('-'),
                        ])
                        ->columns(2),

                    // =====================
                    // HISTORY TAB
                    // =====================
                    Tab::make('History')
                        ->schema([
                            TextEntry::make('created_at')
                                ->label('Created At')
                                ->dateTime('d M Y H:i')
                                ->badge(),

                            TextEntry::make('updated_at')
                                ->label('Last Updated')
                                ->dateTime('d M Y H:i')
                                ->badge(),
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
