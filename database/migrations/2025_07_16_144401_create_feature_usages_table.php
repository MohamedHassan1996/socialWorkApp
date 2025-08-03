<?php

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
        Schema::create('feature_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('feature_id')->constrained()->onDelete('cascade');
            $table->string('scope_type')->nullable(); // 'workspace', 'project', 'team', etc.
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->integer('usage_count')->default(0);
            $table->date('period_start');
            $table->date('period_end');
            $this->CreatedUpdatedByRelationship($table);
            $table->timestamps();

            $table->unique(['user_id', 'feature_id', 'scope_type', 'scope_id', 'period_start'], 'feature_usage_unique');

            // $table->unique(['user_id', 'feature_id', 'scope_type', 'scope_id', 'period_start'], 'feature_usage_unique');
            // $table->index(['user_id', 'period_start']);
            // $table->index(['scope_type', 'scope_id']);
            // $table->index(['feature_id', 'period_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_usages');
    }
};
