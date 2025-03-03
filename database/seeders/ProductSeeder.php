<?php

    namespace Database\Seeders;

    use Illuminate\Database\Seeder;
    use App\Models\Product;
    use Carbon\Carbon;
    use Faker\Factory as Faker;

    class ProductSeeder extends Seeder
    {
        /**
         * Run the database seeds.
         */
        public function run(): void
        {
            $faker = Faker::create();

            // Ensure unique references and SKUs
            $faker->unique(true);

            for ($i = 0; $i < 200; $i++) {
                // Set team_id to 22 for all products
                $teamId = 12;

                // Randomly assign product_unit_id (1 to 4)
                $productUnitId = $faker->numberBetween(1, 4);

                // Generate unique reference and SKU
                $reference = 'P' . $faker->unique()->numerify('####');
                $sku = 'SKU' . $faker->unique()->numerify('###');

                // Randomly decide if the product is active, discontinued, or out_of_stock
                $status = $faker->randomElement(['active', 'discontinued', 'out_of_stock']);

                // Generate min and reorder points based on quantity to ensure logical consistency
                $minStockLevel = $faker->randomNumber(3, false);
                $reorderPoint = $minStockLevel +  $faker->randomNumber(3, false);

                // Optionally set max_stock_level (nullable)
                $maxStockLevel = $faker->optional()->randomNumber(3, false);

                // Optionally set expired_date (nullable)
                $expiredDate = $faker->optional()->dateTimeBetween('+1 week', '+2 years');

                // Optionally set barcode and location
                $barcode = $faker->optional()->ean13;
                $location = $faker->optional()->city;

                // Create the product
                Product::create([
                    'team_id' => $teamId,
                    'reference' => $reference,
                    'name' => $faker->words(3, true), // e.g., "Wireless Mouse"
                    'description' => $faker->sentence, // e.g., "A high-quality wireless mouse with ergonomic design."
                    'price' => $faker->randomNumber(5, false), // Price between $5.00 and $500.00
                    'purchase_price' => $faker->randomNumber(3, false),
                    'expired_date' => $expiredDate ? Carbon::instance($expiredDate) : null,
                    'quantity' => $minStockLevel +$faker->numberBetween(0, 500),
                    'product_unit_id' => $productUnitId,
                    'sku' => $sku,
                    'barcode' => $barcode,
                    'status' => $status,
                    'min_stock_level' => $minStockLevel,
                    'max_stock_level' => $maxStockLevel,
                    'reorder_point' => $reorderPoint,
                    'location' => $location,
                ]);
            }
        }
    }
