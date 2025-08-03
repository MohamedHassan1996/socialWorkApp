<?php

use App\Enums\IsActive;
use App\Enums\SubcriptionPlan\PlanFeatureType;
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
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->tinyInteger('type')->default(PlanFeatureType::COUNTABLE->value);
            $table->string('category')->default('general'); // grouping features
            $table->string('unit')->nullable(); // 'members', 'GB', 'posts', etc.
            //$table->json('metadata')->nullable(); // Additional feature configuration
            $table->boolean('is_active')->default(IsActive::ACTIVE->value);
            $table->integer('sort_order')->default(0);
            $this->CreatedUpdatedByRelationship($table);
            $table->timestamps();
            $table->index(['is_active', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('features');
    }
};
