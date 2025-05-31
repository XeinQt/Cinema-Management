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
        Schema::create('booking', function (Blueprint $table) {
            $table->bigIncrements('booking_id');
            $table->unsignedBigInteger('customer_id')->constrained('customer')->onDelete('cascade');
            $table->unsignedBigInteger('screening_id')->constrained('screening')->onDelete('cascade');
            $table->string('set_number');
            $table->string('status');
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
        Schema::dropIfExists('booking');
    }
};
