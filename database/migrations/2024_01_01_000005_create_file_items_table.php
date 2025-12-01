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
        Schema::create('file_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->nullable()->constrained('folders')->onDelete('cascade');
            $table->foreignId('level_id')->constrained('levels')->onDelete('cascade');
            $table->string('filename');
            $table->string('original_filename');
            $table->text('filepath'); // Full path to file in storage
            $table->string('extension', 10);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size'); // File size in bytes
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_archived')->default(false);
            $table->string('storage_provider', 20)->default('local'); // local, gdrive, s3
            $table->text('external_id')->nullable(); // For cloud storage (Google Drive ID, S3 key, etc.)
            $table->softDeletes();
            $table->timestamps();

            // Index for better performance
            $table->index(['folder_id', 'level_id', 'owner_id', 'is_archived']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_items');
    }
};
