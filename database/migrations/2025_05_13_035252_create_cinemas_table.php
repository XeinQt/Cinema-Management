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
            $table->bigIncrements('cinema_id');
            $table->unsignedBigInteger('mall_id')->constrained('malls')->onDelete('cascade');
            $table->unsignedBigInteger('manager_id')->constrained('managers')->onDelete('cascade');
            $table->string('name');
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
        Schema::dropIfExists('cinemas');
    }
};
