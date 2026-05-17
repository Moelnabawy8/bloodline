<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->enum('blood_type', ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-']);
            $table->enum('location', ['Cairo', 'Giza', 'Alexandria', 'Luxor', 'Aswan', 'Mansoura', 'Qena', 'Sohag'])->nullable();
            $table->unsignedSmallInteger('quantity_required')->default(1);
            $table->enum('urgency_level', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['pending', 'active', 'completed', 'cancelled'])->default('pending');
            $table->string('patient_name')->nullable();
            $table->text('patient_info')->nullable();
            $table->timestamp('request_date')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_requests');
    }
};
