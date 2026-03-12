<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            if (!Schema::hasColumn('quiz_questions', 'option_e')) {
                $table->string('option_e')->nullable()->after('option_d');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            if (Schema::hasColumn('quiz_questions', 'option_e')) {
                $table->dropColumn('option_e');
            }
        });
    }
};
