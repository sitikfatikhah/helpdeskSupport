<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class StatsOverview extends BaseWidget
{
    use HasWidgetShield;

    protected ?string $heading = 'Analytics';

    protected ?string $description = 'An overview of ticket analytics.';

    public ?string $from = null;

    public ?string $to = null;

    public function mount(): void
    {
        $this->from = Session::get('filters.from');
        $this->to = Session::get('filters.to');
    }

    public function updateFilters($payload): void
    {
        $this->from = $payload['from'] ?? null;
        $this->to = $payload['to'] ?? null;
    }

    protected function getStats(): array
{
    $user = Auth::user();

    // Base query
    $query = Ticket::query();

    // Filter sesuai user yang login (jika bukan super_admin)
    if (!$user->hasRole('super_admin')) {
        $query->where('user_id', $user->id);
    }

    // Filter tanggal
    if ($this->from) {
        $query->whereDate('created_at', '>=', $this->from);
    }

    if ($this->to) {
        $query->whereDate('created_at', '<=', $this->to);
    }

    // Hitung statistik berdasarkan query yang sudah difilter
    $total     = $query->count() ?? 0;
    $onProgress = (clone $query)->where('ticket_status', 'on_progress')->count() ?? 0;
    $resolved   = (clone $query)->where('ticket_status', 'resolved')->count() ?? 0;
    $callback   = (clone $query)->whereIn('ticket_status', ['callback', 'monitored', 'other'])->count() ?? 0;

    return [
        Stat::make('Total Tickets', $total)
            ->description('Tickets registered')
            ->descriptionIcon('heroicon-m-rocket-launch')
            ->color('success')
            ->extraAttributes([
                'class' => 'cursor-pointer',
                'wire:click' => "\$dispatch('setStatusFilter', { filter: 'processed' })",
            ]),

        Stat::make('On Progress', $onProgress)
            ->description('Tickets in progress')
            ->descriptionIcon('heroicon-m-arrow-path')
            ->color('warning'),

        Stat::make('Resolved', $resolved)
            ->description('Tickets resolved')
            ->descriptionIcon('heroicon-m-check-badge')
            ->color('danger'),

        Stat::make('Callback / Monitored / Other', $callback)
            ->description('Tickets Callback / Monitored / Other')
            ->descriptionIcon('heroicon-m-cloud-arrow-up')
            ->color('info'),
    ];
}

}
