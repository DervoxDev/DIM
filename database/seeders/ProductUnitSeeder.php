<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProductUnit;
class ProductUnitSeeder extends Seeder
{
/**
* Run the database seeds.
*/
public function run(): void
{
$units = [
['name' => 'liter', 'abbreviation' => 'L'],
            ['name' => 'kilogram', 'abbreviation' => 'kg'],
            ['name' => 'unit', 'abbreviation' => 'unit'],
            ['name' => 'piece', 'abbreviation' => 'pc'],
        ];

        foreach ($units as $unit) {
            ProductUnit::create($unit);
        }
    }
}
