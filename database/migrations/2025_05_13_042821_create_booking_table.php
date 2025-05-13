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
          
           // Primary key
            $table->bigIncrements('booking_id');

            // Foreign key for mall_id, referring to the 'mall_id' column in the malls table
            $table->unsignedBigInteger('customer_id')->constrained('customer')->onDelete('cascade');

            // Foreign key for manager_id, referring to the 'manager_id' column in the managers table
            $table->unsignedBigInteger('screening_id')->constrained('screening')->onDelete('cascade');

            $table->string('set_number');
            $table->string('status');
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
