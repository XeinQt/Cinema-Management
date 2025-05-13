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
        Schema::create('screening', function (Blueprint $table) {
           // Primary key
            $table->bigIncrements('screening_id');

            // Foreign key for mall_id, referring to the 'mall_id' column in the malls table
            $table->unsignedBigInteger('cinema_id')->constrained('cinemas')->onDelete('cascade');

            // Foreign key for manager_id, referring to the 'manager_id' column in the managers table
            $table->unsignedBigInteger('movie_id')->constrained('movies')->onDelete('cascade');

            $table->string('screening_time');
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
        Schema::dropIfExists('screening');
    }
};
