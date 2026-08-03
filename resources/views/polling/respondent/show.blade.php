@extends('adminlte::page')
@php
    $routePrefix = request()->routeIs('siswa.*') ? 'siswa.polling' : 'admin.gtk.polling';
@endphp
@section('title',$polling->title)
@section('content_header')<div class="row mb-2"><div class="col-sm-7"><h1><i class="fas fa-poll-h text-primary mr-2"></i>Isi Polling</h1></div><div class="col-sm-5"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="{{ route($routePrefix.'.index') }}">Polling</a></li><li class="breadcrumb-item active">Isi</li></ol></div></div>@stop
@section('content')
<div class="simansa-polling-fill"><div class="card bg-gradient-primary text-white mb-4 fill-hero"><div class="card-body"><div class="row align-items-center"><div class="col-lg-8"><div class="small text-uppercase font-weight-bold mb-2"><i class="fas fa-clipboard-check mr-1"></i>{{ strtoupper($context['type']) }}</div><h2 class="h3 font-weight-bold mb-2">{{ $polling->title }}</h2><p class="mb-0">{{ $polling->description }}</p></div><div class="col-lg-4 mt-3 mt-lg-0"><div class="deadline"><small>Batas Pengisian</small><strong>{{ $polling->ends_at->format('d/m/Y H:i') }} WIB</strong></div></div></div></div></div>
@if(session('success'))<div class="alert alert-success"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><strong>Jawaban belum dapat disimpan.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@php
    $canEdit = $polling->isOpen() && (! $response || $polling->allow_changes);
@endphp
<form method="POST" action="{{ route($routePrefix.'.store',$polling) }}">@csrf
    @foreach($polling->questions as $index=>$question)
    @php
        $answer = $answerMap->get($question->id);
        $storedAnswer = $answer?->options->isNotEmpty() ? $answer->options->pluck('id')->all() : $answer?->answer_text;
        $selected = old('answers.'.$question->id, $storedAnswer);
    @endphp
    <div class="card card-outline card-primary question-card"><div class="card-header"><h3 class="card-title font-weight-bold"><span class="question-index">{{ $index+1 }}</span>{{ $question->prompt }} @if($question->is_required)<span class="text-danger">*</span>@endif</h3></div><div class="card-body">
        @if(in_array($question->type, ['single', 'yes_no']))
            <div class="option-grid">
                @foreach($question->options as $option)
                    @php
                        $checked = is_array($selected)
                            ? in_array($option->id, $selected)
                            : (string) $selected === $option->id;
                    @endphp
                    <label class="option-card">
                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}" @checked($checked) @disabled(!$canEdit)>
                        <span><i class="far fa-circle option-off"></i><i class="fas fa-check-circle option-on"></i>{{ $option->label }}</span>
                    </label>
                @endforeach
            </div>
        @elseif($question->type === 'multiple')
            <div class="mb-2 small text-muted">
                Pilih {{ $question->min_selections ? 'minimal '.$question->min_selections : '' }}{{ $question->min_selections && $question->max_selections ? ' dan ' : '' }}{{ $question->max_selections ? 'maksimal '.$question->max_selections : '' }} opsi.
            </div>
            <div class="option-grid">
                @foreach($question->options as $option)
                    @php
                        $checked = in_array($option->id, (array) $selected);
                    @endphp
                    <label class="option-card">
                        <input type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $option->id }}" @checked($checked) @disabled(!$canEdit)>
                        <span><i class="far fa-square option-off"></i><i class="fas fa-check-square option-on"></i>{{ $option->label }}</span>
                    </label>
                @endforeach
            </div>
        @elseif($question->type === 'short_text')
            <input class="form-control" name="answers[{{ $question->id }}]" maxlength="500" value="{{ is_string($selected) ? $selected : '' }}" @disabled(!$canEdit)>
        @else
            <textarea class="form-control" name="answers[{{ $question->id }}]" rows="5" maxlength="5000" @disabled(!$canEdit)>{{ is_string($selected) ? $selected : '' }}</textarea>
        @endif
    </div></div>
    @endforeach
    @if($polling->require_consent)<div class="card consent-card"><div class="card-body"><div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input" id="consent" name="consent" value="1" @checked($response) @disabled(!$canEdit)><label class="custom-control-label font-weight-bold" for="consent">{{ $polling->consent_text }}</label></div></div></div>@endif
    <div class="card"><div class="card-body d-flex flex-wrap justify-content-between align-items-center"><a href="{{ route($routePrefix.'.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i>Kembali</a>@if($canEdit)<button class="btn btn-primary btn-lg"><i class="fas fa-paper-plane mr-1"></i>{{ $response?'Perbarui Jawaban':'Kirim Jawaban' }}</button>@else<span class="badge badge-success p-2"><i class="fas fa-lock mr-1"></i>Jawaban sudah tersimpan</span>@endif</div></div>
</form>
@if($response && $polling->show_results_after_submit && $publicStats->isNotEmpty())
    <div class="card card-outline card-primary mt-4">
        <div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-chart-bar text-primary mr-2"></i>Hasil Agregat Sementara</h3></div>
        <div class="card-body">
            @foreach($publicStats as $stat)
                <h6 class="font-weight-bold mt-3">{{ $stat['prompt'] }}</h6>
                @if($stat['options']->isNotEmpty())
                    @php
                        $base = max(1, $stat['answer_count']);
                    @endphp
                    @foreach($stat['options'] as $option)
                        @php
                            $pct = round($option['count'] / $base * 100, 1);
                        @endphp
                        <div class="mb-2">
                            <div class="d-flex justify-content-between small"><span>{{ $option['label'] }}</span><strong>{{ $option['count'] }} ({{ $pct }}%)</strong></div>
                            <div class="progress"><div class="progress-bar" style="width:{{ $pct }}%"></div></div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted">{{ $stat['answer_count'] }} jawaban teks terkumpul.</p>
                @endif
            @endforeach
        </div>
    </div>
@endif
</div>
@stop
@section('css')<style>.simansa-polling-fill{max-width:1100px;margin:0 auto}.simansa-polling-fill .fill-hero{border:0;border-radius:18px;overflow:hidden}.simansa-polling-fill .deadline{background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);border-radius:12px;padding:.8rem 1rem}.simansa-polling-fill .deadline small,.simansa-polling-fill .deadline strong{display:block}.simansa-polling-fill .question-index{align-items:center;background:#dbeafe;border-radius:50%;color:#1d4ed8;display:inline-flex;height:30px;justify-content:center;margin-right:.65rem;width:30px}.simansa-polling-fill .option-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem}.simansa-polling-fill .option-card{margin:0}.simansa-polling-fill .option-card input{position:absolute;opacity:0}.simansa-polling-fill .option-card span{align-items:center;background:#fff;border:1px solid #cbd5e1;border-radius:10px;cursor:pointer;display:flex;gap:.65rem;min-height:52px;padding:.75rem}.simansa-polling-fill .option-card input:checked+span{background:#eff6ff;border-color:#2563eb;color:#1d4ed8;font-weight:700}.simansa-polling-fill .option-on{display:none}.simansa-polling-fill .option-card input:checked+span .option-on{display:inline}.simansa-polling-fill .option-card input:checked+span .option-off{display:none}.simansa-polling-fill .option-card input:disabled+span{cursor:default;opacity:.8}.simansa-polling-fill .consent-card{border-left:4px solid #f59e0b;background:#fffbeb}@media(max-width:575.98px){.simansa-polling-fill .option-grid{grid-template-columns:1fr}.simansa-polling-fill .card-body{padding:1rem}.simansa-polling-fill .card-body.d-flex{gap:.75rem}.simansa-polling-fill .btn-lg{font-size:1rem;width:100%}}</style>@stop
