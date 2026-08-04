@extends('adminlte::page')

@section('plugins.Datatables', true)

@section('title', 'Hasil Polling')

@section('content_header')
<div class="row mb-2"><div class="col-sm-7"><h1><i class="fas fa-chart-bar text-primary mr-2"></i>Hasil Polling</h1></div><div class="col-sm-5"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="{{ route('admin.polling.index') }}">Polling</a></li><li class="breadcrumb-item active">Hasil</li></ol></div></div>
@stop

@section('content')
<div class="simansa-polling-report">
    @if(session('warning'))<div class="alert alert-warning"><i class="fas fa-exclamation-triangle mr-2"></i>{{ session('warning') }}</div>@endif
    <div class="card bg-gradient-primary text-white mb-4 report-hero"><div class="card-body"><div class="row align-items-center"><div class="col-lg-8"><div class="small text-uppercase font-weight-bold mb-2"><i class="fas fa-poll-h mr-1"></i>{{ $polling->audience === 'both' ? 'Siswa & GTK' : strtoupper($polling->audience) }}</div><h2 class="h3 font-weight-bold mb-2">{{ $polling->title }}</h2><div class="small"><i class="far fa-calendar-alt mr-1"></i>{{ $polling->starts_at->format('d/m/Y H:i') }} — {{ $polling->ends_at->format('d/m/Y H:i') }} WIB</div></div><div class="col-lg-4 mt-3 mt-lg-0 text-lg-right"><span class="badge badge-light px-3 py-2 text-uppercase">{{ $polling->phase }}</span></div></div></div></div>

    @if($polling->description)
    <div class="card card-outline card-primary polling-description"><div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-info-circle text-primary mr-2"></i>Informasi Polling</h3></div><div class="card-body rich-description">{!! $polling->description_html !!}</div></div>
    @endif

    <div class="row">
        @foreach([
            ['Target Responden',$report['targetCount'],'fa-bullseye','primary','all'],
            ['Sudah Mengisi',$report['answeredCount'],'fa-check-circle','success','answered'],
            ['Belum Mengisi',$report['pendingCount'],'fa-user-clock','warning','pending'],
            ['Partisipasi',$report['responseRate'].'%','fa-percentage','info','all'],
        ] as [$label,$value,$icon,$color,$status])
        <div class="col-6 col-xl-3"><a href="#respondent-status" class="polling-stat-link" data-status="{{ $status }}" aria-label="Lihat {{ strtolower($label) }}"><div class="info-box bg-white shadow-sm"><span class="info-box-icon bg-{{ $color }}"><i class="fas {{ $icon }}"></i></span><div class="info-box-content"><span class="info-box-text">{{ $label }}</span><span class="info-box-number">{{ $value }}</span><span class="small text-primary">Lihat data <i class="fas fa-arrow-right ml-1"></i></span></div></div></a></div>
        @endforeach
    </div>

    <div class="card card-outline card-primary"><div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-cogs text-primary mr-2"></i>Operasional Polling</h3><div class="card-tools d-flex flex-wrap" style="gap:.35rem"><a href="{{ route('admin.polling.export',$polling) }}" class="btn btn-sm btn-success" data-no-overlay><i class="fas fa-file-excel mr-1"></i>Excel</a><a href="{{ route('admin.polling.pdf',$polling) }}" class="btn btn-sm btn-danger" data-no-overlay><i class="fas fa-file-pdf mr-1"></i>PDF</a>@can('manage-polling')<a href="{{ route('admin.polling.duplicate',$polling) }}" class="btn btn-sm btn-info"><i class="fas fa-copy mr-1"></i>Jadikan Preset</a>@if(!$polling->responses()->exists())<a href="{{ route('admin.polling.edit',$polling) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit mr-1"></i>Edit</a>@endif @if($polling->status==='draft')<form method="POST" action="{{ route('admin.polling.publish',$polling) }}" class="d-inline">@csrf<button class="btn btn-sm btn-primary"><i class="fas fa-paper-plane mr-1"></i>Terbitkan</button></form>@elseif($polling->phase!=='closed')<form method="POST" action="{{ route('admin.polling.close',$polling) }}" class="d-inline confirm-close">@csrf<button class="btn btn-sm btn-secondary"><i class="fas fa-lock mr-1"></i>Tutup</button></form>@endif <form method="POST" action="{{ route('admin.polling.destroy',$polling) }}" class="d-inline confirm-archive">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-secondary" title="Arsipkan"><i class="fas fa-archive"></i></button></form>@endcan</div></div><div class="card-body"><div class="row small"><div class="col-md-4 mb-2"><strong>Tahun ajaran:</strong> {{ $polling->tahun_pelajaran_snapshot ?: 'Belum tercatat' }}{{ $polling->semester_snapshot ? ' · Semester '.$polling->semester_snapshot : '' }}</div><div class="col-md-4 mb-2"><strong>Dibuat:</strong> {{ $polling->created_at->format('d/m/Y H:i') }} WIB</div><div class="col-md-4 mb-2"><strong>Sumber:</strong> {{ $polling->sourcePolling?->title ?: 'Dibuat dari awal' }}</div><div class="col-md-4 mb-2"><strong>Permintaan unlock:</strong> {{ $polling->allow_changes ? 'Diizinkan selama polling dibuka' : 'Tidak diizinkan' }}</div><div class="col-md-4 mb-2"><strong>Hasil untuk responden:</strong> {{ $polling->show_results_after_submit ? 'Ditampilkan' : 'Disembunyikan' }}</div><div class="col-md-4 mb-2"><strong>Persetujuan:</strong> {{ $polling->require_consent ? 'Wajib' : 'Tidak wajib' }}</div></div></div></div>

    <div class="row">
        @foreach($report['questionStats'] as $index=>$stat)
        <div class="col-lg-6"><div class="card card-outline card-primary h-100 mb-4"><div class="card-header"><h3 class="card-title font-weight-bold"><span class="badge badge-primary mr-2">{{ $index+1 }}</span>{{ $stat['question']->prompt }}</h3></div><div class="card-body">
            @if($stat['options']->isNotEmpty())
                @php($base=max(1,$report['answeredCount']))
                @foreach($stat['options'] as $option)
                @php($percentage=round(($option['count']/$base)*100,1))
                <div class="mb-3"><div class="d-flex justify-content-between mb-1"><span>{{ $option['label'] }}</span><button type="button" class="btn btn-link btn-sm p-0 font-weight-bold voter-count" data-url="{{ route('admin.polling.voters', [$polling, $stat['question'], $option['id']]) }}" data-option="{{ $option['label'] }}" title="Lihat data pemilih">{{ $option['count'] }} <small class="text-muted">({{ $percentage }}%)</small> <i class="fas fa-users ml-1"></i></button></div><div class="progress" style="height:10px"><div class="progress-bar bg-primary" style="width:{{ $percentage }}%"></div></div></div>
                @endforeach
            @else
                <div class="text-center py-4"><strong class="display-4 text-primary">{{ $stat['answer_count'] }}</strong><p class="text-muted mb-0">jawaban teks terkumpul</p></div>
            @endif
        </div></div></div>
        @endforeach
    </div>

    <div class="card card-outline card-primary" id="respondent-status"><div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-users text-primary mr-2"></i>Status Responden <span class="badge badge-light ml-2" id="respondentFilterLabel">Semua</span> @if($report['unlockRequestCount'] > 0)<a href="#respondent-status" class="badge badge-warning ml-2 polling-stat-link" data-status="unlock"><i class="fas fa-bell mr-1"></i>{{ $report['unlockRequestCount'] }} minta unlock</a>@endif</h3></div><div class="card-body"><div class="table-responsive"><table class="table table-hover w-100" id="respondentTable"><thead class="bg-light"><tr><th>#</th><th>Responden</th><th>Rombel/Peran</th><th>Status Jawaban</th><th>Waktu</th>@foreach($polling->questions as $question)<th class="question-column">{{ Str::contains(Str::lower($question->prompt), 'mata pelajaran pilihan') ? 'Mapel Pilihan' : Str::limit($question->prompt,35) }}</th>@endforeach<th>Aksi</th></tr></thead></table></div></div></div>

    <div class="modal fade" id="voterModal" tabindex="-1" role="dialog" aria-labelledby="voterModalTitle" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable" role="document"><div class="modal-content"><div class="modal-header"><div><div class="small text-uppercase text-muted font-weight-bold">Data Pemilih</div><h5 class="modal-title font-weight-bold" id="voterModalTitle">Pilihan</h5></div><button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button></div><div class="modal-body"><div class="table-responsive"><table class="table table-hover w-100" id="voterTable"><thead class="bg-light"><tr><th>#</th><th>Pemilih</th><th>Rombel/Peran</th><th>Waktu Memilih</th></tr></thead></table></div></div></div></div></div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
<style>
.simansa-polling-report .report-hero{border:0;border-radius:18px;overflow:hidden}
.simansa-polling-report .rich-description{color:#334155;line-height:1.7}
.simansa-polling-report .rich-description p:last-child,.simansa-polling-report .rich-description ul:last-child,.simansa-polling-report .rich-description ol:last-child{margin-bottom:0}
.simansa-polling-report .info-box{border:1px solid #e2e8f0;border-radius:14px;overflow:hidden}
.simansa-polling-report .info-box-icon{width:62px}
.simansa-polling-report .table td,.simansa-polling-report .table th{vertical-align:middle}
.simansa-polling-report .question-column{min-width:180px;max-width:260px}
.simansa-polling-report .voter-count{text-decoration:none}
.simansa-polling-report .dataTables_filter{text-align:right}
.simansa-polling-report .dataTables_filter input{margin-left:.5rem}
.simansa-polling-report #respondentTable{min-width:980px}
.simansa-polling-report .modal .table{min-width:620px}
.simansa-polling-report .polling-stat-link{color:inherit;display:block;text-decoration:none}.simansa-polling-report .polling-stat-link .info-box{transition:box-shadow .2s ease,transform .2s ease}.simansa-polling-report .polling-stat-link:hover .info-box,.simansa-polling-report .polling-stat-link:focus .info-box{box-shadow:0 .5rem 1rem rgba(15,23,42,.14)!important;transform:translateY(-2px)}
@media(max-width:767.98px){
    .simansa-polling-report .card-header{align-items:flex-start}
    .simansa-polling-report .card-tools{float:none!important;clear:both;width:100%;padding-top:.65rem}
    .simansa-polling-report .card-tools .btn{min-height:40px}
    .simansa-polling-report .dataTables_filter{text-align:left;margin-top:.75rem}
    .simansa-polling-report .dataTables_filter label{display:flex;align-items:center;width:100%;gap:.5rem}
    .simansa-polling-report .dataTables_filter input{width:100%;min-width:0;margin-left:0}
    .simansa-polling-report #respondent-status .card-body{padding:.75rem}
    .simansa-polling-report #respondent-status .table-responsive{overflow-x:visible}
    .simansa-polling-report #respondentTable{width:100%!important;min-width:100%!important;table-layout:auto!important}
    .simansa-polling-report #respondentTable thead th,.simansa-polling-report #respondentTable tbody td{white-space:nowrap}
    .simansa-polling-report #respondentTable .polling-col-respondent{min-width:165px;white-space:normal}
    .simansa-polling-report #respondentTable .polling-col-status{min-width:112px}
    .simansa-polling-report #respondentTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before{background-color:#2563eb;border:0;box-shadow:none;line-height:1rem}
    .simansa-polling-report #respondentTable.dtr-inline.collapsed>tbody>tr.parent>td.dtr-control:before{background-color:#64748b}
    .simansa-polling-report .polling-mobile-detail{display:grid;gap:.65rem;padding:.25rem 0}
    .simansa-polling-report .polling-mobile-detail__item{display:grid;grid-template-columns:minmax(104px,34%) minmax(0,1fr);gap:.7rem;align-items:start;padding-bottom:.55rem;border-bottom:1px solid #e2e8f0}
    .simansa-polling-report .polling-mobile-detail__item:last-child{padding-bottom:0;border-bottom:0}
    .simansa-polling-report .polling-mobile-detail__label{color:#475569;font-size:.71rem;font-weight:800;letter-spacing:.035em;text-transform:uppercase;overflow-wrap:anywhere}
    .simansa-polling-report .polling-mobile-detail__value{min-width:0;color:#0f172a;white-space:normal;overflow-wrap:anywhere}
    .simansa-polling-report .polling-mobile-detail__value .btn{display:inline-flex;min-width:40px;min-height:40px;align-items:center;justify-content:center}
}
</style>
@stop

@section('js')
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>
<script>
$(function(){
    const escapeHtml=value=>$('<div>').text(value??'-').html();
    const tableLanguage={processing:'Memproses...',search:'Cari:',lengthMenu:'Tampilkan _MENU_ data',info:'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',infoEmpty:'Belum ada data',zeroRecords:'Tidak ditemukan data yang sesuai',paginate:{first:'Pertama',last:'Terakhir',next:'Selanjutnya',previous:'Sebelumnya'}};
    const useMobileResponsiveTable=window.matchMedia('(max-width: 767.98px)').matches;
    const mobileDetailsRenderer=function(api,rowIdx,columns){
        const details=$.map(columns,function(column){
            if(!column.hidden)return '';
            return '<div class="polling-mobile-detail__item" data-dt-row="'+column.rowIndex+'" data-dt-column="'+column.columnIndex+'">'
                +'<div class="polling-mobile-detail__label">'+escapeHtml(column.title)+'</div>'
                +'<div class="polling-mobile-detail__value">'+(column.data||'&mdash;')+'</div>'
                +'</div>';
        }).join('');
        return details?$('<div class="polling-mobile-detail"></div>').append(details):false;
    };
    let respondentStatus='all';
    const respondentTable=$('#respondentTable').DataTable({processing:true,serverSide:true,responsive:useMobileResponsiveTable?{details:{type:'inline',target:0,renderer:mobileDetailsRenderer}}:false,searchDelay:350,ajax:{url:'{{ route('admin.polling.respondents',$polling) }}',data:data=>{data.status=respondentStatus}},order:[],pageLength:10,columnDefs:[{responsivePriority:1,targets:1},{responsivePriority:2,targets:3},{responsivePriority:3,targets:2}],columns:[
        {data:'DT_RowIndex',orderable:false,searchable:false,className:'text-center align-middle polling-col-index'},{data:'respondent',name:'name',className:'align-middle polling-col-respondent'},{data:'scope',name:'class_name',className:'align-middle polling-col-scope'},{data:'response_status',orderable:false,searchable:false,className:'align-middle polling-col-status'},{data:'submitted',name:'submitted_at',className:'align-middle polling-col-time'},
        @foreach($polling->questions as $index=>$question){data:'answers',orderable:false,searchable:false,className:'align-middle polling-col-answer',render:(answers)=>escapeHtml((answers||[])[{{ $index }}]||'-')},@endforeach
        {data:'action',orderable:false,searchable:false,className:'text-center align-middle polling-col-action'}
    ],language:tableLanguage});
    $(document).on('click','.polling-stat-link',function(event){event.preventDefault();respondentStatus=$(this).data('status')||'all';const labels={all:'Semua',answered:'Sudah Mengisi',pending:'Belum Mengisi',unlock:'Minta Unlock'};$('#respondentFilterLabel').text(labels[respondentStatus]||'Semua');respondentTable.ajax.reload(()=>document.getElementById('respondent-status').scrollIntoView({behavior:'smooth',block:'start'}),true)});
    let voterTable=null;
    $(document).on('click','.voter-count',function(){const button=$(this);$('#voterModalTitle').text(button.data('option'));$('#voterModal').modal('show');if(voterTable)voterTable.destroy();voterTable=$('#voterTable').DataTable({processing:true,serverSide:true,destroy:true,searchDelay:300,ajax:button.data('url'),order:[],pageLength:10,columns:[{data:'DT_RowIndex',orderable:false,searchable:false},{data:'respondent',name:'respondent_name'},{data:'scope',name:'class_name'},{data:'submitted',name:'submitted_at'}],language:tableLanguage})});
    $(document).on('click','.unlock-response',function(){const button=$(this);Swal.fire({icon:'question',title:'Buka kunci jawaban?',text:button.data('name')+' dapat mengubah jawaban satu kali sebelum terkunci kembali.',showCancelButton:true,confirmButtonText:'Ya, Buka Kunci',cancelButtonText:'Batal',confirmButtonColor:'#2563eb'}).then(result=>{if(!result.isConfirmed)return;$.post(button.data('url'),{_token:'{{ csrf_token() }}'}).done(response=>{Swal.fire({icon:'success',title:'Berhasil',text:response.message,timer:1800,showConfirmButton:false});respondentTable.ajax.reload(null,false)}).fail(xhr=>Swal.fire({icon:'error',title:'Gagal',text:xhr.responseJSON?.message||'Jawaban tidak dapat dibuka.'}))})});
    $('.confirm-close').on('submit',function(e){e.preventDefault();const f=this;Swal.fire({icon:'warning',title:'Tutup polling?',text:'Responden tidak dapat lagi mengirim jawaban.',showCancelButton:true,confirmButtonText:'Ya, tutup',cancelButtonText:'Batal',confirmButtonColor:'#64748b'}).then(r=>{if(r.isConfirmed)f.submit()})});
    $('.confirm-archive').on('submit',function(e){e.preventDefault();const f=this;Swal.fire({icon:'question',title:'Arsipkan polling?',text:'Polling ditutup, tetapi jadwal, hasil, dan konfigurasi tetap tersimpan sebagai riwayat/preset.',showCancelButton:true,confirmButtonText:'Ya, arsipkan',cancelButtonText:'Batal',confirmButtonColor:'#64748b'}).then(r=>{if(r.isConfirmed)f.submit()})});
});
</script>
@stop
