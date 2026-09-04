<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VerificationResource\Pages;
use App\Models\Verification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VerificationResource extends Resource
{
    protected static ?string $model = Verification::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationLabel = 'Vérifications ambiguës';

    protected static ?string $modelLabel = 'vérification ambiguë';

    protected static ?string $pluralModelLabel = 'vérifications ambiguës';

    // N'affiche que les vérifications réellement ambiguës dans toute la Resource
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('statut', 'ambigu');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('diplome_id')
                    ->relationship('diplome', 'numeroDiplome')
                    ->disabled(),
                Forms\Components\TextInput::make('scoreFinal')
                    ->numeric()
                    ->disabled(),
                Forms\Components\Textarea::make('resultat')
                    ->columnSpanFull()
                    ->disabled(),
                Forms\Components\Textarea::make('texteOcrBrut')
                    ->columnSpanFull()
                    ->disabled(),
                Forms\Components\Textarea::make('donneesAnalyseIa')
                    ->columnSpanFull()
                    ->disabled(),
                Forms\Components\Radio::make('statut')
                    ->label('Décision finale')
                    ->options([
                        'authentique' => 'Authentique',
                        'suspect' => 'Suspect',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dateVérification')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('diplome.numeroDiplome')
                    ->label('N° diplôme')
                    ->searchable(),
                Tables\Columns\TextColumn::make('diplome.client.name')
                    ->label('Client')
                    ->searchable(),
                Tables\Columns\TextColumn::make('scoreFinal')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Reçue le')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('resoudre')
                    ->label('Résoudre')
                    ->icon('heroicon-o-check-circle')
                    ->color('warning')
                    ->form([
                        Forms\Components\Radio::make('nouveauStatut')
                            ->label('Décision finale')
                            ->options([
                                'authentique' => 'Authentique',
                                'suspect' => 'Suspect',
                            ])
                            ->required(),
                    ])
                    ->action(function (Verification $record, array $data): void {
                        $record->update(['statut' => $data['nouveauStatut']]);

                        Notification::make()
                            ->title('Vérification résolue')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'asc')
            ->emptyStateHeading('Aucune vérification ambiguë')
            ->emptyStateDescription('Tous les cas litigieux ont été traités.')
            ->emptyStateIcon('heroicon-o-check-badge');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVerifications::route('/'),
           'view' => Pages\ViewVerification::route('/{record}'),
        ];
    }
}
