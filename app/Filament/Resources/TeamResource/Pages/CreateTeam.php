<?php

    namespace App\Filament\Resources\TeamResource\Pages;

    use App\Filament\Resources\TeamResource;
    use Filament\Actions;
    use Filament\Resources\Pages\CreateRecord;
    use App\Models\User;
    use App\Models\Plan;
    use Illuminate\Database\Eloquent\Model;
    class CreateTeam extends CreateRecord
    {
        protected static string $resource = TeamResource::class;
        protected function handleRecordCreation(array $data): Model
        {
            // Create the team first
            $team = static::getModel()::create([
                'name' => $data['name'],
            ]);

            // Handle team admin assignment
            if (isset($data['team_admin_id'])) {
                // Remove any existing team assignments for this admin
                User::where('id', $data['team_admin_id'])
                    ->update(['team_id' => $team->id]);
            }

            // Get the plan details
            $plan = Plan::find($data['plan_id']);

            // Create the subscription
            $team->subscription()->create([
                'plan_id' => $data['plan_id'],
                'subscription_type' => $plan?->name,
                'subscription_expiredDate' => $data['subscription_expiredDate'],
                'subscription_startDate' => now(),
                'status' => $data['subscription']['status'],
            ]);

            return $team->load('subscription');
        }

        protected function getRedirectUrl(): string
        {
            return $this->getResource()::getUrl('index');
        }
    }
