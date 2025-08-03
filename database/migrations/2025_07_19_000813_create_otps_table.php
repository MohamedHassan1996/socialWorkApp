<?php

use App\Enums\Otp\OtpDeliveryMethod;
use App\Enums\Otp\OtpType;
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
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->index(); // Can be email, phone, etc.
            $table->string('otp', 10);
            $table->tinyInteger('type')->default(OtpType::FORGET_PASSWORD->value);
            $table->tinyInteger('delivery_method')->default(OtpDeliveryMethod::EMAIL->value); // Email, SMS, WhatsApp, etc.
            $table->timestamp('expires_at');
            $this->createdUpdatedByRelationship($table);
            $table->timestamps();
            // Composite index for better query performance
            $table->index(['identifier', 'type']);
            $table->index('expires_at');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};
