<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class TicketChartSolved extends ChartWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Chart';

    protected static ?string $maxHeight = '300px';

    public ?string $filter = 'year';

     protected string $periodLabel = '';

    public function getDescription(): ?string
    {
        return "The number of ticket category. Period: {$this->periodLabel}";
    }

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last Week',
            'month' => 'Last Month',
            'year' => 'This Year',
        ];
    }

    protected function getData(): array
{
    $activeFilter = $this->filter;

    // Tentukan rentang tanggal & format
    switch ($activeFilter) {
        case 'today':
            $startDate = now()->startOfDay();
            $endDate   = now()->endOfDay();
            break;

        case 'week':
            $startDate = now()->subWeek()->startOfDay();
            $endDate   = now()->endOfDay();
            break;

        case 'month':
            $startDate = now()->startOfMonth();
            $endDate   = now()->endOfMonth();
            break;

        case 'year':
        default:
            $startDate = now()->startOfYear();
            $endDate   = now()->endOfYear();
            break;
    }

    $this->periodLabel = match ($activeFilter) {
            'today' => $startDate->format('Y-m-d'),
            'week'  => $startDate->format('d M') . ' - ' . $endDate->format('d M Y'),
            'month' => $startDate->format('F Y'),
            'year'  => $startDate->format('Y'),
            default => '',
        };

    // Ambil data tiket
    $query = Ticket::query()
        ->selectRaw("category, COUNT(*) as aggregate")
        ->whereBetween('open_time', [$startDate, $endDate])
        ->groupBy('category');

    // Filter user kalau bukan super_admin
    $user = Auth::user();
    if (!$user->hasRole('super_admin')) {
        $query->where('user_id', $user->id);
    }

    $data = $query->pluck('aggregate', 'category')->toArray();

    // Ambil semua kategori dari palet warna, jangan hanya dari DB
    $colorPallete = [
        'software' => '#3B82F6',
        'hardware' => '#F59E0B',
        'network'  => '#EF4444',
        'other'    => '#10B981',
    ];

    // Label sesuai filter
    $labels = array_keys($colorPallete);

    $values =  array_map(fn ($category) => (int) ($data[$category] ?? 0), $labels);

    return [
        'labels' => array_map('ucfirst', $labels), // Low, Medium, High
        'datasets' => [
            [
                'label' => "Tickets ({$this->periodLabel})",
                'data' => $values,
                'backgroundColor' => array_values($colorPallete),
                'borderColor' => array_values($colorPallete),
                'borderWidth' => 1,
            ],
        ],
    ];


}

protected function getOptions(): array
{
    return [
        'plugins' => [
            'legend' => [
                'display' => true,   // tampilkan legend warna
                'position' => 'bottom',
            ],
        ],
        'title' => [
                'display' => true,
                'text' => "Tickets per categories ({$this->periodLabel})", // judul chart
            ],

        // 'scales' => [
        //     'x' => [
        //         'display' => false, // sembunyikan sumbu X
        //     ],
        //     'y' => [
        //         'display' => false, // sembunyikan sumbu Y
        //     ],
        // ],
    ];
}
    protected function getType(): string
    {
        return 'pie';
    }
}
