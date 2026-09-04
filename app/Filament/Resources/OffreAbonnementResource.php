<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OffreAbonnementResource\Pages;
use App\Models\OffreAbonnement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OffreAbonnementResource extends Resource
{
    protected static ?string $model = OffreAbonnement::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Offres abonnements';

    protected static ?string $modelLabel = 'offre d\'abonnement';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nom')
                    ->label('Nom de l\'offre')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('type')
                    ->label('Type')
                    ->options([
                        'gratuit' => 'Gratuit',
                        'premium' => 'Premium',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('montant')
                    ->label('Montant')
                    ->numeric()
                    ->required()
                    ->suffix(fn (Forms\Get $get) => $get('devise') ?? 'XAF'),

                Forms\Components\Select::make('devise')
                    ->label('Devise')
                    ->options([
                        'XAF' => 'XAF (Franc CFA)',
                    ])
                    ->default('XAF')
                    ->required(),

                Forms\Components\TextInput::make('duree_jours')
                    ->label('Durée (en jours)')
                    ->numeric()
                    ->required()
                    ->suffix('jours')
                    ->helperText('Mets 0 pour une offre gratuite sans expiration.'),

                Forms\Components\TextInput::make('limite_verifications')
                    ->label('Limite de vérifications')
                    ->numeric()
                    ->nullable()
                    ->helperText('Nombre de vérifications autorisées pour cette offre. Laisse vide pour illimité.'),

                Forms\Components\Toggle::make('actif')
                    ->label('Offre active')
                    ->helperText('Seule l\'offre active de chaque type est proposée aux utilisateurs.')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom')
                    ->label('Nom')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'secondary' => 'gratuit',
                        'success' => 'premium',
                    ]),

                Tables\Columns\TextColumn::make('montant')
                    ->label('Montant')
                    ->formatStateUsing(fn ($state, $record) => $state == 0 ? 'Gratuit' : number_format($state, 0, ',', ' ') . ' ' . $record->devise)
                    ->sortable(),

                Tables\Columns\TextColumn::make('duree_jours')
                    ->label('Durée')
                    ->formatStateUsing(fn ($state) => $state == 0 ? '—' : $state . ' jours')
                    ->sortable(),

                Tables\Columns\TextColumn::make('limite_verifications')
                    ->label('Limite vérifications')
                    ->formatStateUsing(fn ($state) => $state === null ? 'Illimité' : $state),

                Tables\Columns\IconColumn::make('actif')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
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
            'index' => Pages\ListOffreAbonnements::route('/'),
            'create' => Pages\CreateOffreAbonnement::route('/create'),
            'edit' => Pages\EditOffreAbonnement::route('/{record}/edit'),
        ];
    }
}