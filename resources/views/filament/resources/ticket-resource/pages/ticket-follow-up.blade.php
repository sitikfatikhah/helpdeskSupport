<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="text-lg font-semibold">Tindak Lanjut — {{ $record->ticket_number }}</h2>
                <p class="text-sm text-gray-500">User: {{ $record->user->name ?? '-' }} • Dept: {{ $record->department->department_name ?? '-' }}</p>
            </div>
            <div class="text-sm text-gray-600">
                Status saat ini: <span class="font-medium">{{ ucfirst($record->ticket_status ?? '–') }}</span>
            </div>
        </div>

        {{-- Form --}}
        <form wire:submit.prevent="save" class="space-y-3">
            {{-- <x-filament::textarea
                wire:model.defer="content"
                placeholder="Tulis tindak lanjut atau catatan di sini..."
                rows="4"
            /> --}}
            <textarea
                wire:model.defer="content"
                placeholder="Tulis tindak lanjut atau catatan di sini..."
                rows="4"
                {{-- Tambahkan kelas styling Filament untuk konsistensi --}}
                class="filament-forms-textarea-component block w-full transition duration-75 rounded-lg shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 disabled:opacity-70 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:border-primary-500"
            ></textarea>
            <div class="flex gap-3 items-center">
                <label class="flex items-center gap-2">
                    <span class="text-sm">Ubah status</span>
                    <select wire:model.defer="ticket_status" class="rounded-md border-gray-200">
                        <option value="">(biarkan)</option>
                        <option value="on_progress">On Progress</option>
                        <option value="resolved">Resolved</option>
                        <option value="callback">Callback</option>
                        <option value="monitored">Monitored</option>
                        <option value="other">Other</option>
                    </select>
                </label>

                <x-filament::button type="submit">Simpan</x-filament::button>
                <a href="{{ \App\Filament\Resources\TicketResource::getUrl('view', ['record' => $record]) }}" class="text-sm text-gray-500 underline">Kembali ke detail</a>
            </div>
        </form>

        {{-- Riwayat tindak lanjut (parsing kolom step_taken) --}}
        <div>
            <h3 class="font-medium mb-2">Riwayat Tindak Lanjut</h3>

            @php
                $raw = $record->step_taken ?? '';
                $parts = $raw === '' ? [] : explode('<hr class="my-3" />', $raw);
            @endphp

            @if (count($parts) === 0)
                <p class="text-sm text-gray-500">Belum ada tindak lanjut.</p>
            @else
                <div class="space-y-3">
                    @foreach ($parts as $part)
                        <div class="p-3 border rounded bg-white">
                            {!! $part !!}
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
