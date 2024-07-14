<?php

namespace App\Filament\Resources\TeamResource\Pages;

use App\Filament\Resources\TeamResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Model;
class EditTeam extends EditRecord
{
    protected static string $resource = TeamResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Remove this method if it's not needed
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);

        // Update the subscription separately
        if ($record->subscription) {
            $record->subscription->update([
                'subscription_type' => $data['subscription_type'] ?? $record->subscription->subscription_type,
                'subscription_expiredDate' => $data['subscription_expiredDate'] ?? $record->subscription->subscription_expiredDate,
            ]);
        } else {
            // Create a new subscription if it doesn't exist
            $record->subscription()->create([
                'subscription_type' => $data['subscription_type'] ?? null,
                'subscription_expiredDate' => $data['subscription_expiredDate'] ?? null,
            ]);
        }

        return $record->fresh('subscription');
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

}
