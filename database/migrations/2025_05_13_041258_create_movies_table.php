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
        Schema::create('movies', function (Blueprint $table) {
            $table->bigIncrements('movie_id'); // Primary key
            $table->string('title');
            $table->string('genre');
            $table->string('duration'); // Removed unique constraint, unless you need it
            $table->string('description')->nullable(); // Make description optional
            $table->string('rating')->nullable(); // Rating can be optional
            $table->timestamps(); // Created_at and updated_at columns

            // Ensure using InnoDB for foreign key constraints
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies'); // Drop the movies table
    }
};
