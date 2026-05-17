<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('national_id', 14)->unique()->nullable();
            $table->tinyInteger('age');
            $table->enum('gender', ['Male', 'Female']);
            $table->string('phone', 20);
            $table->enum('governorate', ['cairo', 'alexandria', 'giza', 'luxor', 'aswan', 'mansoura', 'qena', 'sohag']);
            $table->string('city', 100)->nullable();
            $table->string('address', 255);
            $table->enum('blood_type', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']);
            $table->unsignedSmallInteger('weight');
            $table->unsignedInteger('points')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donors');
    }
};
