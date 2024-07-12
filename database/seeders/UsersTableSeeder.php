<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Team;
use Illuminate\Support\Facades\Hash;
class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = Team::all();
        // Create a specific user
        $specificUser = User::create([
            'name' => 'akram',
            'email' => 'ak@gmail.com',
            'password' => Hash::make('cakram123'), // Hash the password
            'team_id' => $teams->random()->id, // Assign a random team
        ]);

        // Assign roles to the specific user if necessary
        if ($specificUser) {
            $specificUser->assignRole('admin');
        }


        User::factory()->count(50)->make()->each(function ($user) use ($teams) {
            $user->team_id = $teams->random()->id;
            $user->save();
        });


    }
}
