<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class TicketChart extends ChartWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Chart';

    protected static ?string $maxHeight = '300px';

    public ?string $filter = 'year';

    protected string $periodLabel = '';

    public function getDescription(): ?string
    {
        return "The number of tickets per priority level. Period: {$this->periodLabel}";
    }

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last week',
            'month' => 'Last month',
            'year' => 'This year',
        ];
    }


    protected function getData(): array
{
    $activeFilter = $this->filter;

    // tentukan rentang & format query
    switch ($activeFilter) {
        case 'today':
            $startDate  = now()->startOfDay();
            $endDate    = now()->endOfDay();
            break;

        case 'week':
            $startDate  = now()->subWeek()->startOfDay();
            $endDate    = now()->endOfDay();
            break;

        case 'month':
            $startDate  = now()->startOfMonth();
            $endDate    = now()->endOfMonth();
            break;

        case 'year':
        default:
            $startDate  = now()->startOfYear();
            $endDate    = now()->endOfYear();
            break;
    }

    $this->periodLabel = match ($activeFilter) {
            'today' => $startDate->format('Y-m-d'),
            'week'  => $startDate->format('d M') . ' - ' . $endDate->format('d M Y'),
            'month' => $startDate->format('F Y'),
            'year'  => $startDate->format('Y'),
            default => '',
        };

    // query total per priority pakai open_time
    $query = Ticket::query()
        ->selectRaw("priority_level, COUNT(*) as aggregate")
        ->whereBetween('open_time', [$startDate, $endDate])
        ->groupBy('priority_level');

    $user = Auth::user();
    if (!$user->hasRole('super_admin')) {
        $query->where('user_id', $user->id);
    }

    $data = $query->pluck('aggregate', 'priority_level')->toArray();

    // palette
    $colorPalette = [
        'low'    => '#3B82F6',
        'medium' => '#F59E0B',
        'high'   => '#EF4444',
    ];

    // labels sesuai urutan
    $labels = array_keys($colorPalette);

    // values sesuai urutan
    $values =  array_map(fn ($priority) => (int) ($data[$priority] ?? 0), $labels);


    return [
        'labels' => array_map('ucfirst', $labels), // Low, Medium, High
        'datasets' => [
            [
                'label' => "Tickets ({$this->periodLabel})",
                'data' => $values,
                'backgroundColor' => array_values($colorPalette),
                'borderColor' => array_values($colorPalette),
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
                'text' => "Tickets per Priority ({$this->periodLabel})", // judul chart
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
