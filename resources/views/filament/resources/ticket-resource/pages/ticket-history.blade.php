<x-filament-panels::page>
    <div class="space-y-6">
        <div>
            <h2 class="text-lg font-semibold">History — {{ $record->ticket_number }}</h2>
            <p class="text-sm text-gray-500">Dibuat: {{ $record->created_at->format('d-m-Y H:i') }} • Terakhir update: {{ $record->updated_at->format('d-m-Y H:i') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 border rounded">
                <h4 class="font-medium mb-2">Informasi tiket</h4>
                <ul class="text-sm space-y-1">
                    <li><strong>Status:</strong> {{ ucfirst($record->ticket_status ?? '-') }}</li>
                    <li><strong>Priority:</strong> {{ ucfirst($record->priority_level ?? '-') }}</li>
                    <li><strong>Category:</strong> {{ ucfirst($record->category ?? '-') }}</li>
                    <li><strong>Open time:</strong> {{ optional($record->open_time)->format('d-m-Y H:i') ?? '-' }}</li>
                    <li><strong>Close time:</strong> {{ optional($record->close_time)->format('d-m-Y H:i') ?? '-' }}</li>
                </ul>
            </div>

            <div class="p-4 border rounded">
                <h4 class="font-medium mb-2">Perubahan & catatan</h4>
                @php
                    $raw = $record->step_taken ?? '';
                    $parts = $raw === '' ? [] : explode('<hr class="my-3" />', $raw);
                @endphp

                @if (count($parts) === 0)
                    <p class="text-sm text-gray-500">Belum ada history rincian.</p>
                @else
                    <div class="space-y-2 text-sm">
                        @foreach($parts as $part)
                            <div class="p-2 bg-white rounded border">{!! $part !!}</div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
