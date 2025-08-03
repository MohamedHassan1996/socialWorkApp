<?php

use App\Enums\IsActive;
use App\Enums\SubcriptionPlan\PlanBillingCycle;
use App\Traits\CreatedUpdatedByMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use CreatedUpdatedByMigration;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->tinyInteger('billing_cycle')->default(PlanBillingCycle::MONTHLY->value);
            $table->boolean('is_active')->default(IsActive::ACTIVE->value);
            $table->boolean('is_popular')->default(IsActive::IN_ACTIVE->value); // Featured plan
            $table->timestamp('available_from')->nullable();
            $table->timestamp('available_until')->nullable();
            $table->tinyInteger('sort_order')->default(0);
            $this->CreatedUpdatedByRelationship($table);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
