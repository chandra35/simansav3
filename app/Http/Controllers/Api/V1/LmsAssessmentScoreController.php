<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LmsAssessmentScore;
use App\Models\Siswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LmsAssessmentScoreController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'external_event_id' => ['required', 'string', 'max:80'],
            'student_id' => ['required', 'uuid', 'exists:siswa,id'],
            'assessment_type' => ['required', 'in:harian,pts,pas,semester,tugas'],
            'assessment_title' => ['required', 'string', 'max:190'],
            'subject' => ['nullable', 'string', 'max:190'],
            'score' => ['required', 'numeric', 'min:0', 'max:100'],
            'graded_at' => ['required', 'date'],
            'metadata' => ['nullable', 'array'],
        ]);

        Siswa::query()->whereKey($data['student_id'])->where('status_siswa', 'aktif')->firstOrFail();
        $score = LmsAssessmentScore::updateOrCreate(['external_event_id' => $data['external_event_id']], [
            'siswa_id' => $data['student_id'], 'assessment_type' => $data['assessment_type'],
            'assessment_title' => $data['assessment_title'], 'subject' => $data['subject'] ?? null,
            'score' => $data['score'], 'graded_at' => $data['graded_at'], 'payload' => $data['metadata'] ?? null,
        ]);

        return response()->json(['data' => ['id' => $score->id, 'external_event_id' => $score->external_event_id]], 201);
    }
}
