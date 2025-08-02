<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Pgvector\Laravel\Vector;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('knowledges', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->text('text');
            $table->vector('embedding', 768);
            $table->enum('source', ['manual', 'pdf', 'crawl']);

            $table->string('document_id')->nullable();              // ID unik dokumen (jika dari PDF, bisa file_id)
            $table->integer('chunk_index')->nullable();             // urutan chunk ke berapa
            $table->integer('chunk_offset')->nullable();            // token offset awal (opsional)

            $table->jsonb('metadata')->nullable();

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
        Schema::dropIfExists('knowledges');
    }
};
