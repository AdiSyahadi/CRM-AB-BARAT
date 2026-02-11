<div class="space-y-4">
    {{-- Summary --}}
    <div class="grid grid-cols-3 gap-4 p-4 bg-gray-100 dark:bg-gray-800 rounded-lg">
        <div class="text-center">
            <div class="text-2xl font-bold text-primary-600">
                Rp {{ number_format($record->total_donasi ?? 0, 0, ',', '.') }}
            </div>
            <div class="text-sm text-gray-500">Total Donasi</div>
        </div>
        <div class="text-center">
            <div class="text-2xl font-bold text-success-600">
                {{ $record->jml_transaksi ?? 0 }}
            </div>
            <div class="text-sm text-gray-500">Jumlah Transaksi</div>
        </div>
        <div class="text-center">
            <div class="text-2xl font-bold text-warning-600">
                {{ $record->last_donation ? \Carbon\Carbon::parse($record->last_donation)->diffForHumans() : '-' }}
            </div>
            <div class="text-sm text-gray-500">Terakhir Donasi</div>
        </div>
    </div>

    {{-- Riwayat Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-2 text-left">No</th>
                    <th class="px-4 py-2 text-left">Tanggal</th>
                    <th class="px-4 py-2 text-left">Tim</th>
                    <th class="px-4 py-2 text-left">CS</th>
                    <th class="px-4 py-2 text-right">Jumlah</th>
                    <th class="px-4 py-2 text-left">Program</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                @forelse($riwayat as $index => $item)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-4 py-2">{{ $index + 1 }}</td>
                    <td class="px-4 py-2">{{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}</td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 text-xs rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300">
                            {{ $item->tim ?? '-' }}
                        </span>
                    </td>
                    <td class="px-4 py-2">{{ $item->nama_cs ?? '-' }}</td>
                    <td class="px-4 py-2 text-right font-semibold text-success-600">
                        Rp {{ number_format($item->jml_perolehan ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-2">
                        {{ $item->hasil_dari ?? $item->program ?? '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                        Tidak ada riwayat donasi
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(count($riwayat) >= 20)
    <div class="text-center text-sm text-gray-500">
        Menampilkan 20 transaksi terakhir
    </div>
    @endif
</div>
