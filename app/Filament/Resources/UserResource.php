<?php

namespace App\Filament\Resources;

use App\Models\Appartement;
use Filament\Forms;
use App\Models\Unit;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Tables\Columns\Column;
use Illuminate\Support\Facades\Hash;

use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\UserResource\Pages;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        $user = Filament::auth()->user();

        return $form
            ->schema([
                TextInput::make('name')
                    ->required(),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true), // ✅ Email harus unique

                Select::make('level_id')
                    ->relationship('level', 'nama')
                    ->required(),

                TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context) => $context === 'create')
                    ->label('Password'),

                Select::make('appartement_id')
                    ->relationship('appartement', 'nama')
                    ->label('Pilih Appartement')
                    ->options(function () use ($user) {
                        if ($user->level_id === 1) {
                            return Appartement::all()->pluck('nama', 'id');
                        } else {
                            return Appartement::where('id', $user->appartement_id)->pluck('nama', 'id');
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('No')
                    ->state(
                        static function (Column $column, $record, $rowLoop) {
                            return $rowLoop->iteration;
                        }
                    ),
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('level.nama')->label('Level')->searchable(),
                TextColumn::make('appartement.nama')->label('Appartement')->searchable(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
