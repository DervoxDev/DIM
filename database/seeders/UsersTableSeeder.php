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


        User::factory()->count(50)->make()->each(function ($user) use ($teams) {
            $user->team_id = $teams->random()->id;
            $user->save();
        });


    }
}
