<?php

    namespace App\Filament\Resources\TeamResource\Pages;

    use App\Filament\Resources\TeamResource;
    use Filament\Actions;
    use Filament\Resources\Pages\EditRecord;
    use Filament\Forms;
    use Filament\Forms\Form;
    use Illuminate\Database\Eloquent\Model;
    use App\Models\User;
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

            // Get the new team admin ID
            $newTeamAdminId = $data['team_admin_id'] ?? null;

            // First update the team record
            $record->update($data);

            if ($newTeamAdminId) {
                // Remove team_id from all users who are team admins for this team
                User::where('team_id', $record->id)
                    ->whereHas('roles', fn($q) => $q->where('name', 'team_admin'))
                    ->update(['team_id' => null]);

                // Assign the new team admin
                User::where('id', $newTeamAdminId)
                    ->update(['team_id' => $record->id]);
            }


            // Get the plan details
            $plan = \App\Models\Plan::find($data['plan_id']);

            if ($record->subscription) {
                $record->subscription->update([
                    'plan_id' => $data['plan_id'],
                    'subscription_type' => $plan?->name, // Add the plan name as subscription type
                    'subscription_expiredDate' => $data['subscription_expiredDate'],
                    'status' => $data['subscription']['status'],
                ]);
            } else {
                // Create a new subscription if it doesn't exist
                $record->subscription()->create([
                    'plan_id' => $data['plan_id'],
                    'subscription_type' => $plan?->name, // Add the plan name as subscription type
                    'subscription_expiredDate' => $data['subscription_expiredDate'],
                    'subscription_startDate' => now(),
                    'status' => $data['subscription']['status'],
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
        protected function getFormModel(): Model
        {
            return $this->getRecord()->load('subscription');
        }
        protected function fillForm(): void
        {
            $record = $this->getRecord();

            $data = [
                'name' => $record->name,
                'team_admin_id' => $record->users()->whereHas('roles', fn($q) => $q->where('name', 'team_admin'))->first()?->id,
                'subscription_expiredDate' => $record->subscription?->subscription_expiredDate,
                'plan_id' => $record->subscription?->plan_id,
                'subscription' => [
                    'status' => $record->subscription?->status
                ]
            ];

            $this->form->fill($data);
        }
    }
