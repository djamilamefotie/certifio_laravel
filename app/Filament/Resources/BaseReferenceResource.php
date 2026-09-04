<?php

namespace App\Filament\Resources;

use App\Filament\Imports\BaseReferenceImporter;
use App\Filament\Resources\BaseReferenceResource\Pages;
use App\Models\BaseReference;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BaseReferenceResource extends Resource
{
    protected static ?string $model = BaseReference::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Base de référence';

    protected static ?string $modelLabel = 'diplôme de référence';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identification du diplôme')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('numeroDiplome')
                            ->label('Numéro de diplôme')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('typeDiplome')
                            ->label('Type de diplôme')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('institution')
                            ->label('Institution')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('etablissement')
                            ->label('Établissement')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('mention')
                            ->label('Mention')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('serieOuFiliere')
                            ->label('Série / Filière')
                            ->maxLength(255),
                    ]),

                Forms\Components\Section::make('Titulaire')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('nomTitulaire')
                            ->label('Nom du titulaire')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('matricule')
                            ->label('Matricule')
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('dateNaissance')
                            ->label('Date de naissance'),

                        Forms\Components\TextInput::make('lieuNaissance')
                            ->label('Lieu de naissance')
                            ->maxLength(255),
                    ]),

                Forms\Components\Section::make('Obtention')
                    ->columns(2)
                    ->schema([
                        Forms\Components\DatePicker::make('dateObtention')
                            ->label('Date d\'obtention'),

                        Forms\Components\TextInput::make('session')
                            ->label('Session')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('centreExamen')
                            ->label('Centre d\'examen')
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('dateDelivrance')
                            ->label('Date de délivrance'),

                        Forms\Components\TextInput::make('lieuDelivrance')
                            ->label('Lieu de délivrance')
                            ->maxLength(255),
                    ]),

                Forms\Components\Textarea::make('informationReference')
                    ->label('Information complémentaire')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numeroDiplome')
                    ->label('N° diplôme')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('typeDiplome')
                    ->label('Type')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('institution')
                    ->label('Institution')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nomTitulaire')
                    ->label('Titulaire')
                    ->searchable(),

                Tables\Columns\TextColumn::make('dateObtention')
                    ->label('Obtenu le')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('mention')
                    ->label('Mention')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('typeDiplome')
                    ->label('Type de diplôme')
                    ->options(fn () => BaseReference::query()
                        ->distinct()
                        ->pluck('typeDiplome', 'typeDiplome')
                        ->toArray()),

                Tables\Filters\SelectFilter::make('institution')
                    ->label('Institution')
                    ->options(fn () => BaseReference::query()
                        ->distinct()
                        ->pluck('institution', 'institution')
                        ->toArray()),
            ])
            ->headerActions([
                Tables\Actions\ImportAction::make()
                    ->importer(BaseReferenceImporter::class)
                    ->label('Importer'),
            ])
            ->actions([
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
            'index' => Pages\ListBaseReferences::route('/'),
            'create' => Pages\CreateBaseReference::route('/create'),
        ];
    }
}