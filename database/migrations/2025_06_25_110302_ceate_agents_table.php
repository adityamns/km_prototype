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
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->string('access_key')->nullable();
            $table->jsonb('model');
            $table->string('temperature')->default('0.7');
            $table->string('key')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('system_prompt')->nullable();
            $table->jsonb('filters')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(false);
            $table->boolean('is_internal')->default(false);

            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
