<?php

namespace App\Filament\Resources\TransactionRelationManagerResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\RelationManagers\RelationManager;

class TransactionRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    public function form(Form $form): Form
    {
        return $form->schema([
            DatePicker::make('tanggal')->required(),
            TextInput::make('keterangan'),
            TextInput::make('harga')->numeric()->required(),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_invoice'),
                TextColumn::make('tanggal')->date(),
                TextColumn::make('harga')->money('IDR'),
                TextColumn::make('keterangan'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $lastId = Transaction::max('id') + 1;
                        $today = now()->format('Ymd');
                        $data['kode_invoice'] = 'INV-' . $today . '-' . str_pad($lastId, 3, '0', STR_PAD_LEFT);
                        $data['user_id'] = Filament::auth()->user()->id;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('downloadInvoice')
                    ->label('Download Invoice')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn($record) => route('booking.download.invoice', ['booking' => $record->booking_id]))
                    ->openUrlInNewTab(),
            ])

            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

}
