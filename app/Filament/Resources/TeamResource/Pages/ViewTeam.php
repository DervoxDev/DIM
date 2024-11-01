<?php

    namespace App\Filament\Resources\TeamResource\Pages;

    use App\Filament\Resources\TeamResource;
    use Filament\Actions;
    use Filament\Resources\Pages\ViewRecord;
    use Filament\Infolists;
    use Filament\Infolists\Infolist;
    use Filament\Forms;
    use Filament\Forms\Form;
    class ViewTeam extends ViewRecord
    {
        protected static string $resource = TeamResource::class;
        public function infolist(Infolist $infolist): Infolist
        {
            return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('name')
                                              ->label('Team Name'),
                Infolists\Components\TextEntry::make('subscription.plan.name')
                                              ->label('Subscription Type'),
                Infolists\Components\TextEntry::make('subscription.status')
                                              ->label('Subscription Status'),
                Infolists\Components\TextEntry::make('subscription.subscription_expiredDate')
                                              ->label('Subscription Expiry Date')
                                              ->date(),
                Infolists\Components\TextEntry::make('teamAdmin.name')
                                              ->label('Team Admin')
                                              ->getStateUsing(fn ($record) => $record->users()->whereHas('roles', fn($q) => $q->where('name', 'team_admin'))->first()?->name ?? 'Not Assigned'),
            ]);
        }
        public function form(Form $form): Form
        {
            return TeamResource::form($form);
        }
        public function getActions(): array
        {
            return [
                Actions\EditAction::make(),
            ];
        }

        protected function getFormModel(): string
        {
            return $this->getRecord()::class;
        }

    }
