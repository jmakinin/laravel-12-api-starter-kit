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
        Schema::create('media_assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('uploaded_by');
            $table->string('file_type'); // e.g. profile, document, general
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_url');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size')->nullable(); // file size in bytes
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users');

            // Add indexes for better performance
            $table->index(['user_id', 'file_type']);
            $table->index('uploaded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_assets');
    }
};
