<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use Filament\Forms;
use Illuminate\Support\Carbon;
use App\Models\Booking;
use Filament\Widgets\Widget;

class BookingStatsLivewire extends Widget implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    public ?string $tanggal = null;
    protected static string $view = 'livewire.booking-stats-livewire';


    public function mount(): void
    {
        $this->tanggal = now()->format('Y-m-d');
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\DatePicker::make('tanggal')
                ->label('Pilih Tanggal')
                ->reactive()
                ->afterStateUpdated(fn() => $this->dispatch('$refresh'))
                ->default($this->tanggal),
        ];
    }

    public function render(): View
    {
        $date = Carbon::parse($this->tanggal);
        $user = auth()->user();

        $query = Booking::whereDate('tanggal', $date);

        if ($user->can('admin-local') || $user->can('admin-global')) {
            $query->whereHas('user', fn($q) => $q->where('appartement_id', $user->appartement_id));
        }

        $total = $query->count();
        $cash = (clone $query)->sum('harga_cash');
        $transfer = (clone $query)->sum('harga_transfer');
        $totalMasuk = $cash + $transfer;

        return view('livewire.booking-stats-livewire', [
            'total' => $total,
            'cash' => $cash,
            'transfer' => $transfer,
            'totalMasuk' => $totalMasuk,
            'date' => $date,
            'form' => $this->form,
        ]);

    }
}
