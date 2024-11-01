<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        /**
         * Run the migrations.
         */
        public function up(): void
        {
            // Create plans table
            Schema::create('plans', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->decimal('price', 10, 2);
                $table->enum('duration_type', ['monthly', 'yearly', 'lifetime']);
                $table->integer('duration_value')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Create plan features table
            Schema::create('plan_features', function (Blueprint $table) {
                $table->id();
                $table->foreignId('plan_id')->constrained()->onDelete('cascade');
                $table->string('feature_name');
                $table->string('feature_value');
                $table->string('feature_type')->default('boolean'); // boolean, numeric, text
                $table->timestamps();
            });

            // Modify existing subscriptions table
            Schema::table('subscriptions', function (Blueprint $table) {
                // Add new columns
                $table->foreignId('plan_id')->nullable()->after('team_id');
                $table->enum('status', ['active', 'expired', 'cancelled'])->default('active')->after('subscription_expiredDate');

                // Add foreign key
                $table->foreign('plan_id')->references('id')->on('plans')->onDelete('set null');
            });

            // Modify teams table to add active status
            Schema::table('teams', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('name');
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            // Remove modifications from subscriptions table
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->dropForeign(['plan_id']);
                $table->dropColumn(['plan_id', 'status']);
            });

            // Remove is_active from teams table
            Schema::table('teams', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });

            // Drop new tables
            Schema::dropIfExists('plan_features');
            Schema::dropIfExists('plans');
        }
    };
