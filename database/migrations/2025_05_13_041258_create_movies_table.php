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
            $table->bigIncrements('movie_id');
            $table->string('title');
            $table->string('genre');
            $table->string('duration'); 
            $table->string('description')->nullable(); 
            $table->string('rating')->nullable(); 
            $table->boolean('active')->default(1); 
            $table->timestamps();

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
