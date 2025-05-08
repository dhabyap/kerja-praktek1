<div>
    <div class="mb-6">
        {{ $form }}
    </div>

    testtasdsadasdas
    <x-filament::widgets.stats-overview>
        <x-filament::widgets.stats-overview.card label="Total Booking Hari Ini" value="{{ $total }}"
            description="{{ $date->format('d M Y') }}" color="info" />

        <x-filament::widgets.stats-overview.card label="Total Pemasukan"
            value="Rp {{ number_format($totalMasuk, 0, ',', '.') }}" color="success" />

        <x-filament::widgets.stats-overview.card label="Total Cash" value="Rp {{ number_format($cash, 0, ',', '.') }}"
            color="primary" />

        <x-filament::widgets.stats-overview.card label="Total Transfer"
            value="Rp {{ number_format($transfer, 0, ',', '.') }}" color="danger" />
    </x-filament::widgets.stats-overview>
</div>