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
        Schema::create('managers', function (Blueprint $table) {
            $table->bigIncrements('manager_id'); 
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phonenumber')->nullable();
              $table->boolean('active')->default(1); // or default(0) if you prefer
            $table->timestamps();

            $table->engine = 'InnoDB';

        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      Schema::dropIfExists('managers'); // This should be 'managers'

    }
};
