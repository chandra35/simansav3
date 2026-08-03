@extends('adminlte::page')

@section('title', 'Hasil Polling')

@section('content_header')
<div class="row mb-2"><div class="col-sm-7"><h1><i class="fas fa-chart-bar text-primary mr-2"></i>Hasil Polling</h1></div><div class="col-sm-5"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="{{ route('admin.polling.index') }}">Polling</a></li><li class="breadcrumb-item active">Hasil</li></ol></div></div>
@stop

@section('content')
<div class="simansa-polling-report">
    @if(session('warning'))<div class="alert alert-warning"><i class="fas fa-exclamation-triangle mr-2"></i>{{ session('warning') }}</div>@endif
    <div class="card bg-gradient-primary text-white mb-4 report-hero"><div class="card-body"><div class="row align-items-center"><div class="col-lg-8"><div class="small text-uppercase font-weight-bold mb-2"><i class="fas fa-poll-h mr-1"></i>{{ $polling->audience === 'both' ? 'Siswa & GTK' : strtoupper($polling->audience) }}</div><h2 class="h3 font-weight-bold mb-2">{{ $polling->title }}</h2><p class="mb-2">{{ $polling->description ?: 'Tidak ada deskripsi.' }}</p><div class="small"><i class="far fa-calendar-alt mr-1"></i>{{ $polling->starts_at->format('d/m/Y H:i') }} — {{ $polling->ends_at->format('d/m/Y H:i') }} WIB</div></div><div class="col-lg-4 mt-3 mt-lg-0 text-lg-right"><span class="badge badge-light px-3 py-2 text-uppercase">{{ $polling->phase }}</span></div></div></div></div>

    <div class="row">
        @foreach([
            ['Target Responden',$report['targetCount'],'fa-bullseye','primary'],
            ['Sudah Mengisi',$report['answeredCount'],'fa-check-circle','success'],
            ['Belum Mengisi',$report['pendingCount'],'fa-user-clock','warning'],
            ['Partisipasi',$report['responseRate'].'%','fa-percentage','info'],
        ] as [$label,$value,$icon,$color])
        <div class="col-6 col-xl-3"><div class="info-box bg-white shadow-sm"><span class="info-box-icon bg-{{ $color }}"><i class="fas {{ $icon }}"></i></span><div class="info-box-content"><span class="info-box-text">{{ $label }}</span><span class="info-box-number">{{ $value }}</span></div></div></div>
        @endforeach
    </div>

    <div class="card card-outline card-primary"><div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-cogs text-primary mr-2"></i>Operasional Polling</h3><div class="card-tools d-flex flex-wrap" style="gap:.35rem"><a href="{{ route('admin.polling.export',$polling) }}" class="btn btn-sm btn-success" data-no-overlay><i class="fas fa-file-excel mr-1"></i>Excel</a><a href="{{ route('admin.polling.pdf',$polling) }}" class="btn btn-sm btn-danger" data-no-overlay><i class="fas fa-file-pdf mr-1"></i>PDF</a>@can('manage-polling')<a href="{{ route('admin.polling.duplicate',$polling) }}" class="btn btn-sm btn-info"><i class="fas fa-copy mr-1"></i>Jadikan Preset</a>@if(!$polling->responses()->exists())<a href="{{ route('admin.polling.edit',$polling) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit mr-1"></i>Edit</a>@endif @if($polling->status==='draft')<form method="POST" action="{{ route('admin.polling.publish',$polling) }}" class="d-inline">@csrf<button class="btn btn-sm btn-primary"><i class="fas fa-paper-plane mr-1"></i>Terbitkan</button></form>@elseif($polling->phase!=='closed')<form method="POST" action="{{ route('admin.polling.close',$polling) }}" class="d-inline confirm-close">@csrf<button class="btn btn-sm btn-secondary"><i class="fas fa-lock mr-1"></i>Tutup</button></form>@endif <form method="POST" action="{{ route('admin.polling.destroy',$polling) }}" class="d-inline confirm-archive">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-secondary" title="Arsipkan"><i class="fas fa-archive"></i></button></form>@endcan</div></div><div class="card-body"><div class="row small"><div class="col-md-4 mb-2"><strong>Tahun ajaran:</strong> {{ $polling->tahun_pelajaran_snapshot ?: 'Belum tercatat' }}{{ $polling->semester_snapshot ? ' · Semester '.$polling->semester_snapshot : '' }}</div><div class="col-md-4 mb-2"><strong>Dibuat:</strong> {{ $polling->created_at->format('d/m/Y H:i') }} WIB</div><div class="col-md-4 mb-2"><strong>Sumber:</strong> {{ $polling->sourcePolling?->title ?: 'Dibuat dari awal' }}</div><div class="col-md-4 mb-2"><strong>Perubahan jawaban:</strong> {{ $polling->allow_changes ? 'Diizinkan sampai ditutup' : 'Tidak diizinkan' }}</div><div class="col-md-4 mb-2"><strong>Hasil untuk responden:</strong> {{ $polling->show_results_after_submit ? 'Ditampilkan' : 'Disembunyikan' }}</div><div class="col-md-4 mb-2"><strong>Persetujuan:</strong> {{ $polling->require_consent ? 'Wajib' : 'Tidak wajib' }}</div></div></div></div>

    <div class="row">
        @foreach($report['questionStats'] as $index=>$stat)
        <div class="col-lg-6"><div class="card card-outline card-primary h-100 mb-4"><div class="card-header"><h3 class="card-title font-weight-bold"><span class="badge badge-primary mr-2">{{ $index+1 }}</span>{{ $stat['question']->prompt }}</h3></div><div class="card-body">
            @if($stat['options']->isNotEmpty())
                @php($base=max(1,$report['answeredCount']))
                @foreach($stat['options'] as $option)
                @php($percentage=round(($option['count']/$base)*100,1))
                <div class="mb-3"><div class="d-flex justify-content-between mb-1"><span>{{ $option['label'] }}</span><strong>{{ $option['count'] }} <small class="text-muted">({{ $percentage }}%)</small></strong></div><div class="progress" style="height:10px"><div class="progress-bar bg-primary" style="width:{{ $percentage }}%"></div></div></div>
                @endforeach
            @else
                <div class="text-center py-4"><strong class="display-4 text-primary">{{ $stat['answer_count'] }}</strong><p class="text-muted mb-0">jawaban teks terkumpul</p></div>
            @endif
        </div></div></div>
        @endforeach
    </div>

    <div class="card card-outline card-primary"><div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-users text-primary mr-2"></i>Status Responden</h3><div class="card-tools"><div class="input-group input-group-sm" style="width:240px"><input id="respondentSearch" class="form-control" placeholder="Cari nama atau rombel"><div class="input-group-append"><span class="input-group-text"><i class="fas fa-search"></i></span></div></div></div></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0" id="respondentTable"><thead class="bg-light"><tr><th>#</th><th>Responden</th><th>Rombel/Peran</th><th>Status</th><th>Waktu</th>@foreach($polling->questions as $question)<th class="question-column">{{ Str::limit($question->prompt,35) }}</th>@endforeach</tr></thead><tbody>@forelse($report['rows'] as $i=>$row)<tr><td>{{ $i+1 }}</td><td><strong>{{ $row['name'] }}</strong><div class="small text-muted">{{ $row['username'] }}</div></td><td>{{ $row['class_name'] ?: strtoupper($row['type']) }}@if($row['grade'])<div class="small text-muted">Tingkat {{ $row['grade'] }}</div>@endif</td><td><span class="badge badge-{{ $row['answered']?'success':'warning' }}">{{ $row['answered']?'Sudah':'Belum' }}</span></td><td class="small text-nowrap">{{ $row['submitted_at']?->format('d/m/Y H:i') ?: '-' }}</td>@foreach($polling->questions as $question)<td class="small">{{ $row['answers'][$question->id] ?: '-' }}</td>@endforeach</tr>@empty<tr><td colspan="{{ 5+$polling->questions->count() }}" class="text-center text-muted py-5">Tidak ada responden yang cocok dengan target.</td></tr>@endforelse</tbody></table></div></div></div>
</div>
@stop

@section('css')
<style>
.simansa-polling-report .report-hero{border:0;border-radius:18px;overflow:hidden}.simansa-polling-report .info-box{border:1px solid #e2e8f0;border-radius:14px;overflow:hidden}.simansa-polling-report .info-box-icon{width:62px}.simansa-polling-report .table td,.simansa-polling-report .table th{vertical-align:middle}.simansa-polling-report .question-column{min-width:180px;max-width:260px}@media(max-width:767.98px){.simansa-polling-report .card-header{align-items:flex-start}.simansa-polling-report .card-tools{float:none!important;clear:both;padding-top:.65rem}}
</style>
@stop

@section('js')
<script>$(function(){$('#respondentSearch').on('input',function(){const q=this.value.toLowerCase();$('#respondentTable tbody tr').each(function(){$(this).toggle($(this).text().toLowerCase().includes(q))})});$('.confirm-close').on('submit',function(e){e.preventDefault();const f=this;Swal.fire({icon:'warning',title:'Tutup polling?',text:'Responden tidak dapat lagi mengirim jawaban.',showCancelButton:true,confirmButtonText:'Ya, tutup',cancelButtonText:'Batal',confirmButtonColor:'#64748b'}).then(r=>{if(r.isConfirmed)f.submit()})});$('.confirm-archive').on('submit',function(e){e.preventDefault();const f=this;Swal.fire({icon:'question',title:'Arsipkan polling?',text:'Polling ditutup, tetapi jadwal, hasil, dan konfigurasi tetap tersimpan sebagai riwayat/preset.',showCancelButton:true,confirmButtonText:'Ya, arsipkan',cancelButtonText:'Batal',confirmButtonColor:'#64748b'}).then(r=>{if(r.isConfirmed)f.submit()})})});</script>
@stop
