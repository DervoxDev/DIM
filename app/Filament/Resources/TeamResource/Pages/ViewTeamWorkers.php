<?php

namespace App\Filament\Resources\TeamResource\Pages;

use App\Filament\Resources\TeamResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables;
use Filament\Tables\Table;
class ViewTeamWorkers extends ViewRecord implements Tables\Contracts\HasTable
{
    protected static string $resource = TeamResource::class;
    use Tables\Concerns\InteractsWithTable;

    protected static string $view = 'filament.resources.team-resource.pages.view-team-workers';

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                return $this->getRecord()
                    ->users()
                    ->whereHas('roles', fn($q) => $q->where('name', 'worker'))
                    ->getQuery();
            })
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                // Add any filters here if necessary
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
    public function getTitle(): string
    {
        return "Workers of {$this->getRecord()->name}";
    }
}
