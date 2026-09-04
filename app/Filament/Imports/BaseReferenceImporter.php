<?php

namespace App\Filament\Imports;

use App\Models\BaseReference;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class BaseReferenceImporter extends Importer
{
    protected static ?string $model = BaseReference::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('numeroDiplome')
                ->label('Numéro de diplôme')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('typeDiplome')
                ->label('Type de diplôme')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('institution')
                ->label('Institution')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('nomTitulaire')
                ->label('Nom du titulaire')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('dateNaissance')
                ->label('Date de naissance')
                ->rules(['nullable', 'date']),

            ImportColumn::make('lieuNaissance')
                ->label('Lieu de naissance')
                ->rules(['nullable', 'string', 'max:255']),

            ImportColumn::make('mention')
                ->label('Mention')
                ->rules(['nullable', 'string', 'max:255']),

            ImportColumn::make('serieOuFiliere')
                ->label('Série / Filière')
                ->rules(['nullable', 'string', 'max:255']),

            ImportColumn::make('dateObtention')
                ->label('Date d\'obtention')
                ->rules(['nullable', 'date']),

            ImportColumn::make('session')
                ->label('Session')
                ->rules(['nullable', 'string', 'max:255']),

            ImportColumn::make('dateDelivrance')
                ->label('Date de délivrance')
                ->rules(['nullable', 'date']),

            ImportColumn::make('lieuDelivrance')
                ->label('Lieu de délivrance')
                ->rules(['nullable', 'string', 'max:255']),

            ImportColumn::make('matricule')
                ->label('Matricule')
                ->rules(['nullable', 'string', 'max:255']),

            ImportColumn::make('etablissement')
                ->label('Établissement')
                ->rules(['nullable', 'string', 'max:255']),

            ImportColumn::make('centreExamen')
                ->label('Centre d\'examen')
                ->rules(['nullable', 'string', 'max:255']),

            ImportColumn::make('informationReference')
                ->label('Information complémentaire')
                ->rules(['nullable', 'string']),
        ];
    }

    public function resolveRecord(): ?BaseReference
    {
        // Si un diplôme avec ce numéro + cette institution existe déjà,
        // on le met à jour au lieu de créer un doublon.
        return BaseReference::firstOrNew([
            'numeroDiplome' => $this->data['numeroDiplome'],
            'institution' => $this->data['institution'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Votre import de la base de référence est terminé : ' . number_format($import->successful_rows) . ' ' . str('ligne')->plural($import->successful_rows) . ' importée(s).';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('ligne')->plural($failedRowsCount) . ' en échec.';
        }

        return $body;
    }
}