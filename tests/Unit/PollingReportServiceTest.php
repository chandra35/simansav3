<?php

namespace Tests\Unit;

use App\Models\Polling;
use App\Models\PollingAnswer;
use App\Models\PollingOption;
use App\Models\PollingQuestion;
use App\Models\PollingResponse;
use App\Services\PollingAudienceService;
use App\Services\PollingReportService;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class PollingReportServiceTest extends TestCase
{
    public function test_summary_orders_choice_results_by_highest_vote_and_uses_original_order_for_ties(): void
    {
        $optionA = $this->option('option-a', 'Mapel A', 0);
        $optionB = $this->option('option-b', 'Mapel B', 1);
        $optionC = $this->option('option-c', 'Mapel C', 2);
        $optionD = $this->option('option-d', 'Mapel D', 3);

        $question = new PollingQuestion(['type' => 'multiple', 'sort_order' => 0]);
        $question->id = 'question-1';
        $question->setRelation('options', new Collection([$optionA, $optionB, $optionC, $optionD]));

        $polling = new Polling(['audience' => 'siswa']);
        $polling->id = 'polling-1';
        $polling->setRelation('questions', new Collection([$question]));
        $polling->setRelation('targets', new Collection());
        $polling->setRelation('responses', new Collection([
            $this->response('response-1', 'user-1', $question, [$optionB, $optionC]),
            $this->response('response-2', 'user-2', $question, [$optionB]),
        ]));

        $audience = Mockery::mock(PollingAudienceService::class);
        $audience->shouldReceive('targetRespondents')->once()->with($polling)->andReturn(collect());

        $summary = (new PollingReportService($audience))->summary($polling);
        $options = $summary['questionStats']->first()['options'];

        $this->assertSame(['Mapel B', 'Mapel C', 'Mapel A', 'Mapel D'], $options->pluck('label')->all());
        $this->assertSame([2, 1, 0, 0], $options->pluck('count')->all());
        $this->assertArrayNotHasKey('rows', $summary);
    }

    private function option(string $id, string $label, int $sortOrder): PollingOption
    {
        $option = new PollingOption(['label' => $label, 'sort_order' => $sortOrder]);
        $option->id = $id;
        return $option;
    }

    private function response(string $id, string $userId, PollingQuestion $question, array $options): PollingResponse
    {
        $answer = new PollingAnswer(['polling_question_id' => $question->id]);
        $answer->setRelation('options', new Collection($options));

        $response = new PollingResponse(['user_id' => $userId]);
        $response->id = $id;
        $response->setRelation('answers', new Collection([$answer]));
        return $response;
    }
}
