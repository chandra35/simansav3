<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Polling;
use App\Models\PollingAnswer;
use App\Models\PollingNotificationState;
use App\Models\PollingResponse;
use App\Services\PollingAudienceService;
use App\Services\PollingReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PollingResponseController extends Controller
{
    public function __construct(
        private PollingAudienceService $audience,
        private PollingReportService $reports,
    ) {}

    public function index(Request $request)
    {
        $this->context($request);
        $active = $this->audience->activeForUser($request->user());
        $history = PollingResponse::query()->with('polling')->where('user_id', $request->user()->id)
            ->latest('submitted_at')->get();

        return view('polling.respondent.index', compact('active', 'history'));
    }

    public function show(Request $request, Polling $polling)
    {
        $context = $this->context($request);
        abort_unless($this->audience->isEligible($polling, $request->user()), 403);

        $response = $polling->responses()->with('answers.options')->where('user_id', $request->user()->id)->first();
        abort_if(! $response && ! $polling->isOpen(), 403, 'Polling belum dibuka atau sudah ditutup.');

        $polling->load('questions.options');
        $answerMap = $response?->answers->keyBy('polling_question_id') ?? collect();
        $publicStats = $response && $polling->show_results_after_submit
            ? $this->reports->publicStats($polling)
            : collect();

        return view('polling.respondent.show', compact(
            'polling', 'response', 'answerMap', 'publicStats', 'context'
        ));
    }

    public function store(Request $request, Polling $polling)
    {
        $context = $this->context($request);
        abort_unless($this->audience->isEligible($polling, $request->user()), 403);
        abort_unless($polling->isOpen(), 422, 'Waktu pengisian polling sudah berakhir atau belum dimulai.');

        $existing = $polling->responses()->where('user_id', $request->user()->id)->first();
        if ($existing && ! $polling->allow_changes) {
            throw ValidationException::withMessages(['polling' => 'Jawaban polling ini sudah dikirim dan tidak dapat diubah.']);
        }
        if ($polling->require_consent && ! $request->boolean('consent')) {
            throw ValidationException::withMessages(['consent' => 'Anda harus menyetujui pernyataan sebelum mengirim jawaban.']);
        }

        $polling->load('questions.options');
        $validatedAnswers = $this->validateAnswers($request, $polling);

        $response = DB::transaction(function () use ($polling, $request, $context, $existing, $validatedAnswers) {
            $response = $existing ?: new PollingResponse();
            $response->fill([
                'polling_id' => $polling->id,
                'user_id' => $request->user()->id,
                'respondent_type' => $context['type'],
                'respondent_id' => $context['id'],
                'respondent_name' => $context['name'],
                'class_id' => $context['class_id'],
                'class_name' => $context['class_name'],
                'grade' => $context['grade'],
                'submitted_at' => now(),
            ])->save();

            $response->answers()->delete();
            foreach ($validatedAnswers as $questionId => $answerData) {
                $answer = PollingAnswer::create([
                    'polling_response_id' => $response->id,
                    'polling_question_id' => $questionId,
                    'answer_text' => $answerData['text'],
                ]);
                if ($answerData['options']) $answer->options()->sync($answerData['options']);
            }

            PollingNotificationState::query()->where([
                'polling_id' => $polling->id,
                'user_id' => $request->user()->id,
            ])->delete();

            return $response;
        });

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'activity_type' => $existing ? 'update_polling_response' : 'submit_polling_response',
            'model_type' => PollingResponse::class,
            'model_id' => $response->id,
            'description' => ($existing ? 'Memperbarui' : 'Mengirim').' respons polling '.$polling->title.'.',
            'ip_address' => $request->ip(), 'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(), 'method' => $request->method(),
        ]);

        return redirect($this->audience->respondentRoute($request->user(), $polling))
            ->with('success', $existing ? 'Jawaban berhasil diperbarui.' : 'Jawaban berhasil dikirim. Terima kasih.');
    }

    public function snooze(Request $request, Polling $polling)
    {
        $this->context($request);
        abort_unless($polling->isOpen() && $this->audience->isEligible($polling, $request->user()), 403);
        abort_if($polling->responses()->where('user_id', $request->user()->id)->exists(), 422, 'Polling sudah diisi.');

        $state = PollingNotificationState::firstOrNew([
            'polling_id' => $polling->id,
            'user_id' => $request->user()->id,
        ]);
        $state->last_prompted_at = now();
        $state->snoozed_until = now()->addHours($polling->reminder_interval_hours);
        $state->dismiss_count = ((int) $state->dismiss_count) + 1;
        $state->save();

        return response()->json(['success' => true, 'snoozed_until' => $state->snoozed_until->toIso8601String()]);
    }

    private function validateAnswers(Request $request, Polling $polling): array
    {
        $input = $request->input('answers', []);
        $errors = [];
        $validated = [];

        foreach ($polling->questions as $question) {
            $value = $input[$question->id] ?? null;
            $key = 'answers.'.$question->id;

            if (in_array($question->type, ['single', 'yes_no'], true)) {
                $optionId = is_string($value) ? $value : null;
                if ($question->is_required && ! $optionId) $errors[$key] = 'Pertanyaan ini wajib dijawab.';
                if ($optionId && ! $question->options->contains('id', $optionId)) $errors[$key] = 'Pilihan jawaban tidak valid.';
                if ($optionId) $validated[$question->id] = ['text' => null, 'options' => [$optionId]];
                continue;
            }

            if ($question->type === 'multiple') {
                $optionIds = collect(is_array($value) ? $value : [])->filter()->unique()->values();
                $minimum = $question->min_selections ?: ($question->is_required ? 1 : 0);
                $maximum = $question->max_selections ?: $question->options->count();
                if ($optionIds->count() < $minimum) $errors[$key] = "Pilih minimal {$minimum} jawaban.";
                if ($optionIds->count() > $maximum) $errors[$key] = "Pilih maksimal {$maximum} jawaban.";
                if ($optionIds->diff($question->options->pluck('id'))->isNotEmpty()) $errors[$key] = 'Terdapat pilihan jawaban yang tidak valid.';
                if ($optionIds->isNotEmpty()) $validated[$question->id] = ['text' => null, 'options' => $optionIds->all()];
                continue;
            }

            $text = trim(is_string($value) ? $value : '');
            $limit = $question->type === 'short_text' ? 500 : 5000;
            if ($question->is_required && $text === '') $errors[$key] = 'Pertanyaan ini wajib dijawab.';
            if (mb_strlen($text) > $limit) $errors[$key] = "Jawaban maksimal {$limit} karakter.";
            if ($text !== '') $validated[$question->id] = ['text' => $text, 'options' => []];
        }

        if ($errors) throw ValidationException::withMessages($errors);
        return $validated;
    }

    private function context(Request $request): array
    {
        $context = $this->audience->respondentContext($request->user());
        abort_unless($context, 403);
        return $context;
    }
}
