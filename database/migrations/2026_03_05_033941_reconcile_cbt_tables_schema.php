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
        Schema::table('quizzes', function (Blueprint $table) {
            // Rename duration to duration_minutes if duration exists and duration_minutes doesn't
            if (Schema::hasColumn('quizzes', 'duration') && !Schema::hasColumn('quizzes', 'duration_minutes')) {
                $table->renameColumn('duration', 'duration_minutes');
            }
            
            // Add is_active if it doesn't exist
            if (!Schema::hasColumn('quizzes', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('end_time');
            }
        });

        Schema::table('quiz_questions', function (Blueprint $table) {
            // Rename question to question_text
            if (Schema::hasColumn('quiz_questions', 'question') && !Schema::hasColumn('quiz_questions', 'question_text')) {
                $table->renameColumn('question', 'question_text');
            }

            // Add options A-D if missing
            if (!Schema::hasColumn('quiz_questions', 'option_a')) {
                $table->string('option_a')->after('question_text')->nullable();
                $table->string('option_b')->after('option_a')->nullable();
                $table->string('option_c')->after('option_b')->nullable();
                $table->string('option_d')->after('option_c')->nullable();
            }

            // Correct answer might need adjustment from text to string
            // No changes needed if it's already there but we'll ensure it's compatible
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            if (Schema::hasColumn('quizzes', 'duration_minutes')) {
                $table->renameColumn('duration_minutes', 'duration');
            }
            if (Schema::hasColumn('quizzes', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });

        Schema::table('quiz_questions', function (Blueprint $table) {
            if (Schema::hasColumn('quiz_questions', 'question_text')) {
                $table->renameColumn('question_text', 'question');
            }
            if (Schema::hasColumn('quiz_questions', 'option_a')) {
                $table->dropColumn(['option_a', 'option_b', 'option_c', 'option_d']);
            }
        });
    }
};
