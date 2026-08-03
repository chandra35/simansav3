<?php

namespace App\Services;

use App\Models\Polling;
use Illuminate\Support\Collection;

class PollingReportService
{
    public function __construct(private PollingAudienceService $audience) {}

    public function build(Polling $polling): array
    {
        $polling->loadMissing([
            'questions.options',
            'targets',
            'responses.answers.options',
            'responses.answers.question',
            'responses.user',
        ]);

        $targets = $this->audience->targetRespondents($polling);
        $knownUsers = $targets->pluck('user_id');
        foreach ($polling->responses->whereNotIn('user_id', $knownUsers->all()) as $historicalResponse) {
            $targets->push([
                'type' => $historicalResponse->respondent_type,
                'id' => $historicalResponse->respondent_id,
                'name' => $historicalResponse->respondent_name,
                'class_id' => $historicalResponse->class_id,
                'class_name' => $historicalResponse->class_name,
                'grade' => $historicalResponse->grade,
                'gtk_type' => null,
                'roles' => [],
                'user_id' => $historicalResponse->user_id,
                'username' => $historicalResponse->user?->username ?: '-',
            ]);
        }
        $targets = $targets->sortBy(fn ($row) => ($row['class_name'] ?? 'ZZZ').'|'.$row['name'])->values();
        $responses = $polling->responses->keyBy('user_id');
        $rows = $targets->map(function (array $target) use ($responses, $polling) {
            $response = $responses->get($target['user_id']);
            $answers = collect($polling->questions)->mapWithKeys(function ($question) use ($response) {
                $answer = $response?->answers->firstWhere('polling_question_id', $question->id);
                $display = $answer
                    ? ($answer->options->isNotEmpty() ? $answer->options->pluck('label')->implode(', ') : $answer->answer_text)
                    : null;
                return [$question->id => $display];
            })->all();

            return array_merge($target, [
                'response' => $response,
                'answered' => (bool) $response,
                'submitted_at' => $response?->submitted_at,
                'locked' => $response?->isLocked() ?? false,
                'unlock_requested_at' => $response?->unlock_requested_at,
                'answers' => $answers,
            ]);
        });

        $questionStats = $polling->questions->map(function ($question) use ($polling) {
            if (! in_array($question->type, ['single', 'multiple', 'yes_no'], true)) {
                return [
                    'question' => $question,
                    'answer_count' => $polling->responses->filter(
                        fn ($response) => filled($response->answers->firstWhere('polling_question_id', $question->id)?->answer_text)
                    )->count(),
                    'options' => collect(),
                ];
            }

            $counts = collect($question->options)->mapWithKeys(fn ($option) => [$option->id => 0]);
            foreach ($polling->responses as $response) {
                $answer = $response->answers->firstWhere('polling_question_id', $question->id);
                foreach ($answer?->options ?? [] as $option) {
                    $counts[$option->id] = ($counts[$option->id] ?? 0) + 1;
                }
            }

            return [
                'question' => $question,
                'answer_count' => $polling->responses->filter(
                    fn ($response) => (bool) $response->answers->firstWhere('polling_question_id', $question->id)
                )->count(),
                'options' => $question->options->map(fn ($option) => [
                    'id' => $option->id,
                    'label' => $option->label,
                    'count' => $counts[$option->id] ?? 0,
                ]),
            ];
        });

        $targetCount = $targets->count();
        $answeredCount = $rows->where('answered', true)->count();

        return [
            'targets' => $targets,
            'rows' => $rows,
            'questionStats' => $questionStats,
            'targetCount' => $targetCount,
            'answeredCount' => $answeredCount,
            'pendingCount' => max(0, $targetCount - $answeredCount),
            'responseRate' => $targetCount ? round(($answeredCount / $targetCount) * 100, 1) : 0,
        ];
    }

    public function publicStats(Polling $polling): Collection
    {
        return collect($this->build($polling)['questionStats'])->map(function ($stat) {
            return [
                'prompt' => $stat['question']->prompt,
                'type' => $stat['question']->type,
                'options' => collect($stat['options'])->values(),
                'answer_count' => $stat['answer_count'],
            ];
        });
    }
}
