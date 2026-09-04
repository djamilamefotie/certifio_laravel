<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InstitutionResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class InstitutionResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'Institutions';

    protected static ?string $modelLabel = 'institution';

    // N'affiche que les utilisateurs de catégorie "institut"
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('categorie', 'institut');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom de l\'institution')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Forms\Components\Select::make('statut')
                    ->label('Statut')
                    ->options([
                        'actif' => 'Actif',
                        'bloque' => 'Bloqué',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('password')
                    ->label('Mot de passe')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->maxLength(255),

                Forms\Components\Hidden::make('categorie')
                    ->default('institut'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('statut')
                    ->label('Statut')
                    ->colors([
                        'success' => 'actif',
                        'danger' => 'bloque',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Inscrit le')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'actif' => 'Actif',
                        'bloque' => 'Bloqué',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('bloquer')
                    ->label('Bloquer')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => $record->statut === 'actif')
                    ->action(function (User $record) {
                        $record->update(['statut' => 'bloque']);
                        Notification::make()->title('Compte bloqué')->success()->send();
                    }),

                Tables\Actions\Action::make('debloquer')
                    ->label('Débloquer')
                    ->icon('heroicon-o-lock-open')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => $record->statut === 'bloque')
                    ->action(function (User $record) {
                        $record->update(['statut' => 'actif']);
                        Notification::make()->title('Compte débloqué')->success()->send();
                    }),

                Tables\Actions\EditAction::make()->label('Modifier'),
                Tables\Actions\DeleteAction::make()->label('Supprimer'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInstitutions::route('/'),
            'create' => Pages\CreateInstitution::route('/create'),
            'edit' => Pages\EditInstitution::route('/{record}/edit'),
        ];
    }
}