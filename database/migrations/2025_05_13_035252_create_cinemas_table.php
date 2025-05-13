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
        Schema::create('cinemas', function (Blueprint $table) {
            // Primary key
            $table->bigIncrements('cinema_id');

            // Foreign key for mall_id, referring to the 'mall_id' column in the malls table
            $table->unsignedBigInteger('mall_id')->constrained('malls')->onDelete('cascade');

            // Foreign key for manager_id, referring to the 'manager_id' column in the managers table
            $table->unsignedBigInteger('manager_id')->constrained('managers')->onDelete('cascade');

            $table->string('name');
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
        Schema::dropIfExists('cinemas');
    }
};
