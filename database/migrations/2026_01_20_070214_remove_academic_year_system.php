<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove academic_year_id from classes table
        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn('academic_year_id');
        });

        // Drop academic_years table
        Schema::dropIfExists('academic_years');
    }

    public function down(): void
    {
        // Recreate academic_years table
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('year');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        // Add academic_year_id back to classes
        Schema::table('classes', function (Blueprint $table) {
            $table->foreignId('academic_year_id')->after('id')->constrained('academic_years')->onDelete('cascade');
        });
    }
};
