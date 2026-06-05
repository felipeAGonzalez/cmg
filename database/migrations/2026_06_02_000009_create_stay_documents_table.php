<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stay_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_id')->constrained();
            $table->string('status')->default('pending'); // pending | completed | not_applicable
            $table->json('form_data')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->timestamps();

            // Evita duplicar el mismo documento en una estancia.
            $table->unique(['stay_id', 'document_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stay_documents');
    }
};
