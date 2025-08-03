<?php

use App\Enums\SubcriptionPlan\SubcriptionStatus;
use App\Enums\SubcriptionPlan\SubcriptionType;
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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->tinyInteger('status')->default(SubcriptionStatus::ACTIVE->value);
            $table->string('payment_method')->nullable();
            $table->string('external_id')->nullable(); // For payment processor reference
            $this->CreatedUpdatedByRelationship($table);
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
