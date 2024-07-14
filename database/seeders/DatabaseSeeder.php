<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\Team;
use App\Models\Subscription;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);
        // Create test user if it doesn't exist
        User::firstOrCreate(
            ['email' => 'admin@dervox.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        )->assignRole('admin');

        // Create teams with team leaders and workers
        $numberOfTeams = 5;  // Adjust this number as needed
        $workersPerTeam = 3; // Adjust this number as needed

        for ($i = 1; $i <= $numberOfTeams; $i++) {
            $team = Team::create(['name' => "Team $i"]);

            // Create team leader
            $teamLeader = User::factory()->create([
                'name' => "Team $i Leader",
                'email' => "leader{$i}@example.com",
            ]);
            $teamLeader->assignRole('team_admin');
            $teamLeader->team_id = $team->id;
            $teamLeader->save();

            // Create workers for the team
            User::factory()->count($workersPerTeam)->create([
                'team_id' => $team->id,
            ])->each(function ($user) {
                $user->assignRole('worker');
            });

            // Create subscription for the team
            Subscription::factory()->create([
                'team_id' => $team->id,
            ]);
        }
    }
}
