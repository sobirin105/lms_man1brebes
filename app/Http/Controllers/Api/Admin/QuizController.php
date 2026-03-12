<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    // --- Quiz CRUD ---

    public function index()
    {
        $quizzes = Quiz::with(['class', 'subject', 'teacher'])->latest()->get();
        return response()->json([
            'success' => true,
            'data' => $quizzes
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'is_active' => 'boolean'
        ]);

        $validated['teacher_id'] = auth()->id();
        
        $quiz = Quiz::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Ujian berhasil dibuat',
            'data' => $quiz->load(['class', 'subject'])
        ], 201);
    }

    public function show(Quiz $quiz)
    {
        $quiz->load(['questions', 'class', 'subject']);
        return response()->json([
            'success' => true,
            'data' => $quiz
        ]);
    }

    public function update(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'is_active' => 'boolean'
        ]);

        $quiz->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Ujian berhasil diperbarui',
            'data' => $quiz->load(['class', 'subject'])
        ]);
    }

    public function destroy(Quiz $quiz)
    {
        $quiz->delete();
        return response()->json([
            'success' => true,
            'message' => 'Ujian berhasil dihapus'
        ]);
    }

    // --- Question CRUD ---

    public function getQuestions(Quiz $quiz)
    {
        return response()->json([
            'success' => true,
            'data' => $quiz->questions
        ]);
    }

    public function storeQuestion(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'option_a' => 'required|string',
            'option_b' => 'required|string',
            'option_c' => 'required|string',
            'option_d' => 'required|string',
            'correct_answer' => 'required|in:a,b,c,d'
        ]);

        $question = $quiz->questions()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Soal berhasil ditambahkan',
            'data' => $question
        ], 201);
    }

    public function updateQuestion(Request $request, Quiz $quiz, QuizQuestion $question)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'option_a' => 'required|string',
            'option_b' => 'required|string',
            'option_c' => 'required|string',
            'option_d' => 'required|string',
            'correct_answer' => 'required|in:a,b,c,d'
        ]);

        $question->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Soal berhasil diperbarui',
            'data' => $question
        ]);
    }

    public function destroyQuestion(Quiz $quiz, QuizQuestion $question)
    {
        $question->delete();
        return response()->json([
            'success' => true,
            'message' => 'Soal berhasil dihapus'
        ]);
    }
}
