<?php

    namespace Database\Seeders;

    use Illuminate\Database\Seeder;
    use App\Models\Plan;
    use App\Models\PlanFeature;

    class PlanSeeder extends Seeder
    {
        public function run(): void
        {
            // Create Basic Monthly Plan
            $basicMonthly = Plan::create([
                'name' => 'Basic Monthly',
                'description' => 'Perfect for small teams',
                'price' => 9.99,
                'duration_type' => 'monthly',
                'duration_value' => 1,
            ]);

            // Create Basic Yearly Plan
            $basicYearly = Plan::create([
                'name' => 'Basic Yearly',
                'description' => 'Perfect for small teams with annual billing',
                'price' => 99.99,
                'duration_type' => 'yearly',
                'duration_value' => 1,
            ]);

            // Create Premium Monthly Plan
            $premiumMonthly = Plan::create([
                'name' => 'Premium Monthly',
                'description' => 'Perfect for growing teams',
                'price' => 29.99,
                'duration_type' => 'monthly',
                'duration_value' => 1,
            ]);

            // Create Premium Yearly Plan
            $premiumYearly = Plan::create([
                'name' => 'Premium Yearly',
                'description' => 'Perfect for growing teams with annual billing',
                'price' => 299.99,
                'duration_type' => 'yearly',
                'duration_value' => 1,
            ]);

            // Create Trial Plan
            $trialPlan = Plan::create([
                'name' => 'Trial',
                'description' => 'Free 1-week trial for new users',
                'price' => 0.00, // Free trial
                'duration_type' => 'days',
                'duration_value' => 7,
                'is_active' => true,
            ]);

            // Add features for Basic plans
            $basicFeatures = [
                [
                    'feature_name' => 'max_users',
                    'feature_value' => '5',
                    'feature_type' => 'numeric'
                ],
                [
                    'feature_name' => 'storage_limit_gb',
                    'feature_value' => '10',
                    'feature_type' => 'numeric'
                ],
                [
                    'feature_name' => 'api_access',
                    'feature_value' => 'false',
                    'feature_type' => 'boolean'
                ],
            ];

            foreach ([$basicMonthly, $basicYearly] as $plan) {
                foreach ($basicFeatures as $feature) {
                    PlanFeature::create(array_merge(
                        $feature,
                        ['plan_id' => $plan->id]
                    ));
                }
            }

            // Add features for Premium plans
            $premiumFeatures = [
                [
                    'feature_name' => 'max_users',
                    'feature_value' => '15',
                    'feature_type' => 'numeric'
                ],
                [
                    'feature_name' => 'storage_limit_gb',
                    'feature_value' => '50',
                    'feature_type' => 'numeric'
                ],
                [
                    'feature_name' => 'api_access',
                    'feature_value' => 'true',
                    'feature_type' => 'boolean'
                ],
            ];

            foreach ([$premiumMonthly, $premiumYearly] as $plan) {
                foreach ($premiumFeatures as $feature) {
                    PlanFeature::create(array_merge(
                        $feature,
                        ['plan_id' => $plan->id]
                    ));
                }
            }

            // Optionally, add any features specific to the Trial plan
            $trialFeatures = [
                [
                    'feature_name' => 'max_users',
                    'feature_value' => '3',
                    'feature_type' => 'numeric'
                ],
                [
                    'feature_name' => 'storage_limit_gb',
                    'feature_value' => '5',
                    'feature_type' => 'numeric'
                ],
            ];

            foreach ($trialFeatures as $feature) {
                PlanFeature::create(array_merge(
                    $feature,
                    ['plan_id' => $trialPlan->id]
                ));
            }
        }
    }
