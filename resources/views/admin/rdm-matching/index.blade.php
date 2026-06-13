@extends('adminlte::page')

@section('title', 'Matching Siswa RDM - SIMANSA')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-random"></i> Matching Data Siswa RDM</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('admin.rdm-sync.index') }}" class="btn btn-secondary">
                    <i class="fas fa-sync-alt mr-1"></i> Integrasi RDM
                </a>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card card-primary card-outline shadow-sm">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="card-title mb-0">
          <i class="fas fa-search-plus mr-2 text-primary"></i>Analisis Kecocokan Data Siswa
        </h3>
        <div>
          @if($activeTahun)
            <span class="badge badge-primary px-3 py-2" style="font-size:.82rem;">
              <i class="fas fa-calendar-alt mr-1"></i>Tahun Ajaran RDM: <strong>{{ $activeTahun->tahunajaran_nama }}</strong>
            </span>
          @else
            <span class="badge badge-warning px-3 py-2">Tidak ada tahun ajaran aktif di RDM</span>
          @endif
        </div>
      </div>
      <div class="card-body">

        {{-- Info Banner --}}
        <div class="rdm-info-banner mb-4">
          <div class="rdm-info-banner__icon"><i class="fas fa-info-circle"></i></div>
          <div class="rdm-info-banner__body">
            <div class="rdm-info-banner__title">Tentang Fitur Matching</div>
            <div class="rdm-info-banner__text">
              Membandingkan siswa di <strong>RDM (Rapor Digital Madrasah)</strong> dengan <strong>SIMANSA</strong>
              melalui 3 metode: <strong>NIS</strong> (instan, tanpa decrypt), <strong>NISN</strong>,
              dan <strong>Smart Fuzzy Name</strong> (Levenshtein + Jaccard + Subset detection).
            </div>
            <div class="rdm-info-banner__tags mt-2">
              <span class="rdm-tag rdm-tag--blue"><i class="fas fa-bolt mr-1"></i>NIS — paling cepat</span>
              <span class="rdm-tag rdm-tag--purple"><i class="fas fa-fingerprint mr-1"></i>NISN exact match</span>
              <span class="rdm-tag rdm-tag--amber"><i class="fas fa-magic mr-1"></i>Fuzzy nama = 60%</span>
            </div>
          </div>
        </div>

        {{-- Filter & Run --}}
        <div class="row align-items-end mb-4" style="row-gap:.5rem;">
          <div class="col-auto">
            <label class="rdm-label" for="selectTingkat">Filter Tingkat</label>
            <select id="selectTingkat" class="form-control rdm-select">
              @foreach($tingkatOptions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-auto">
            <button id="btnRunMatching" class="btn btn-primary btn-lg rdm-run-btn">
              <i class="fas fa-play mr-2"></i> Jalankan Matching
            </button>
          </div>
          <div class="col-auto text-muted" style="font-size:.82rem; padding-bottom:.45rem;">
            <i class="fas fa-clock mr-1"></i>Estimasi: <strong>5–60 detik</strong> (cache aktif setelah run pertama)
          </div>
        </div>

        {{-- Loading --}}
        <div id="loadingArea" style="display:none;">
          <div class="rdm-loading-card">
            <div class="rdm-loading-card__spinner"><div class="rdm-spinner"></div></div>
            <div class="rdm-loading-card__body">
              <div class="rdm-loading-card__title" id="loadingTitle">Menyiapkan proses…</div>
              <div class="rdm-loading-card__sub">Mohon tunggu, jangan refresh halaman</div>
              <div class="rdm-loading-steps mt-3">
                <div class="rdm-step" id="step1"><span class="rdm-step__dot"></span><span>Mengambil data RDM</span></div>
                <div class="rdm-step" id="step2"><span class="rdm-step__dot"></span><span>Pre-match via NIS</span></div>
                <div class="rdm-step" id="step3"><span class="rdm-step__dot"></span><span>Dekripsi nama</span></div>
                <div class="rdm-step" id="step4"><span class="rdm-step__dot"></span><span>Smart fuzzy matching</span></div>
                <div class="rdm-step" id="step5"><span class="rdm-step__dot"></span><span>Menyusun hasil</span></div>
              </div>
            </div>
          </div>
        </div>

        {{-- Error --}}
        <div id="errorArea" style="display:none;">
          <div class="rdm-error-card">
            <div class="rdm-error-card__icon"><i class="fas fa-exclamation-circle"></i></div>
            <div>
              <div class="rdm-error-card__title">Terjadi Kesalahan</div>
              <div id="errorMsg" class="rdm-error-card__msg"></div>
            </div>
            <button class="btn btn-sm btn-outline-danger ml-auto" onclick="$('#errorArea').hide()"><i class="fas fa-times"></i></button>
          </div>
        </div>

        {{-- Results --}}
        <div id="resultArea" style="display:none;">

          <div class="row mb-4" id="summaryCards"></div>

          <div class="rdm-section-label mb-3"><span><i class="fas fa-table mr-1"></i>Detail Hasil Matching</span></div>

          <div class="rdm-tab-wrapper">
            <ul class="nav rdm-tabs" id="matchingTabs" role="tablist">
              <li class="nav-item">
                <a class="nav-link rdm-tab active" data-toggle="tab" href="#tabRdmOnly" role="tab">
                  <i class="fas fa-exclamation-circle mr-1"></i>Belum di SIMANSA
                  <span class="rdm-badge rdm-badge--red ml-1" id="badgeRdmOnly">0</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link rdm-tab" data-toggle="tab" href="#tabFuzzy" role="tab">
                  <i class="fas fa-magic mr-1"></i>Verifikasi Nama
                  <span class="rdm-badge rdm-badge--amber ml-1" id="badgeFuzzy">0</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link rdm-tab" data-toggle="tab" href="#tabSimansaOnly" role="tab">
                  <i class="fas fa-user-slash mr-1"></i>Tidak di RDM
                  <span class="rdm-badge rdm-badge--orange ml-1" id="badgeSimansaOnly">0</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link rdm-tab" data-toggle="tab" href="#tabMatched" role="tab">
                  <i class="fas fa-check-circle mr-1"></i>Cocok
                  <span class="rdm-badge rdm-badge--green ml-1" id="badgeMatched">0</span>
                </a>
              </li>
            </ul>

            <div class="tab-content rdm-tab-content" id="matchingTabContent">

              {{-- Tab: Belum di SIMANSA --}}
              <div class="tab-pane fade show active" id="tabRdmOnly" role="tabpanel">
                <div class="rdm-tab-desc rdm-tab-desc--red">
                  <i class="fas fa-exclamation-triangle mr-1"></i>
                  Siswa di RDM yang <strong>tidak ditemukan sama sekali</strong> di SIMANSA (NISN, NIS, maupun nama). Perlu verifikasi dan penambahan segera.
                </div>
                <div class="table-responsive">
                  <table id="tableRdmOnly" class="table table-sm table-hover rdm-table">
                    <thead><tr>
                      <th style="width:3rem">No</th><th>Nama Siswa (RDM)</th>
                      <th style="width:8rem">NIS</th><th style="width:8rem">NISN</th>
                      <th style="width:6rem">Tingkat</th><th style="width:7rem">Kelas</th>
                      <th style="width:3rem">L/P</th><th style="width:8rem">Tgl Lahir</th>
                    </tr></thead>
                    <tbody id="bodyRdmOnly"></tbody>
                  </table>
                </div>
              </div>

              {{-- Tab: Verifikasi Nama (Fuzzy) --}}
              <div class="tab-pane fade" id="tabFuzzy" role="tabpanel">
                <div class="rdm-tab-desc rdm-tab-desc--amber">
                  <i class="fas fa-magic mr-1"></i>
                  Siswa RDM yang <strong>tidak cocok via NISN/NIS</strong> tetapi ada kandidat nama mirip di SIMANSA.
                  Klik chip untuk buka detail siswa.
                  <span class="ml-1">
                    <span class="fuzzy-chip fuzzy-chip--high" style="pointer-events:none;font-size:.72rem;">? =88%</span>
                    <span class="fuzzy-chip fuzzy-chip--medium" style="pointer-events:none;font-size:.72rem;">?? =72%</span>
                    <span class="fuzzy-chip fuzzy-chip--low" style="pointer-events:none;font-size:.72rem;">? =60%</span>
                  </span>
                </div>
                <div class="table-responsive">
                  <table id="tableFuzzy" class="table table-sm table-hover rdm-table">
                    <thead><tr>
                      <th style="width:3rem">No</th><th>Nama Siswa (RDM)</th>
                      <th style="width:8rem">NISN</th><th style="width:9rem">Tingkat / Kelas</th>
                      <th>Kandidat di SIMANSA <small class="font-weight-normal text-muted">(klik untuk detail)</small></th>
                    </tr></thead>
                    <tbody id="bodyFuzzy"></tbody>
                  </table>
                </div>
              </div>

              {{-- Tab: Tidak di RDM --}}
              <div class="tab-pane fade" id="tabSimansaOnly" role="tabpanel">
                <div class="rdm-tab-desc rdm-tab-desc--orange">
                  <i class="fas fa-user-slash mr-1"></i>
                  Siswa SIMANSA yang <strong>tidak ditemukan di RDM</strong>. Bisa jadi belum diinput ke RDM atau NISN/NIS berbeda.
                </div>
                <div class="table-responsive">
                  <table id="tableSimansaOnly" class="table table-sm table-hover rdm-table">
                    <thead><tr>
                      <th style="width:3rem">No</th><th>Nama Siswa (SIMANSA)</th>
                      <th style="width:8rem">NISN</th><th style="width:7rem">Kelas</th>
                      <th style="width:8rem">Data Lengkap</th><th style="width:5rem">Aksi</th>
                    </tr></thead>
                    <tbody id="bodySimansaOnly"></tbody>
                  </table>
                </div>
              </div>

              {{-- Tab: Cocok --}}
              <div class="tab-pane fade" id="tabMatched" role="tabpanel">
                <div class="rdm-tab-desc rdm-tab-desc--green">
                  <i class="fas fa-check-circle mr-1"></i>
                  Siswa yang ditemukan di kedua sistem. Cocok via <strong>NIS</strong> atau <strong>NISN</strong>.
                </div>
                <div class="table-responsive">
                  <table id="tableMatched" class="table table-sm table-hover rdm-table">
                    <thead><tr>
                      <th style="width:3rem">No</th><th>Nama (RDM)</th><th>Nama (SIMANSA)</th>
                      <th style="width:8rem">NISN</th><th style="width:6rem">NIS</th>
                      <th style="width:7rem">Kelas RDM</th><th style="width:8rem">Kelas SIMANSA</th>
                      <th style="width:6rem">Via</th><th style="width:8rem">Data Lengkap</th>
                      <th style="width:5rem">Aksi</th>
                    </tr></thead>
                    <tbody id="bodyMatched"></tbody>
                  </table>
                </div>
              </div>

            </div>{{-- /tab-content --}}
          </div>{{-- /rdm-tab-wrapper --}}

        </div>{{-- /resultArea --}}
      </div>{{-- /card-body --}}
    </div>{{-- /card --}}
  </div>
</div>
@endsection

@section('css')
<link rel="stylesheet" href="//cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
<style>
.rdm-info-banner{display:flex;align-items:flex-start;gap:1rem;background:linear-gradient(135deg,#eff6ff,#f0fdf4);border:1px solid #bfdbfe;border-left:4px solid #3b82f6;border-radius:10px;padding:1rem 1.2rem;}
.rdm-info-banner__icon{font-size:1.4rem;color:#3b82f6;padding-top:.1rem;flex-shrink:0;}
.rdm-info-banner__title{font-weight:700;color:#1e3a5f;font-size:.92rem;margin-bottom:.25rem;}
.rdm-info-banner__text{color:#374151;font-size:.85rem;line-height:1.5;}
.rdm-tag{display:inline-flex;align-items:center;gap:.25rem;padding:.2rem .55rem;border-radius:999px;font-size:.77rem;font-weight:600;margin-right:.3rem;}
.rdm-tag--blue{background:#dbeafe;color:#1d4ed8;} .rdm-tag--purple{background:#ede9fe;color:#7c3aed;} .rdm-tag--amber{background:#fef3c7;color:#b45309;}

.rdm-label{font-size:.82rem;font-weight:600;color:#6b7280;display:block;margin-bottom:.3rem;text-transform:uppercase;letter-spacing:.04em;}
.rdm-select{border:1.5px solid #d1d5db;border-radius:8px;padding:.45rem .75rem;font-size:.88rem;background:#f9fafb;transition:border-color .2s;min-width:160px;}
.rdm-select:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.1);outline:none;}
.rdm-run-btn{border-radius:10px;padding:.55rem 1.5rem;font-weight:600;letter-spacing:.02em;box-shadow:0 4px 12px rgba(59,130,246,.3);transition:transform .1s,box-shadow .15s;}
.rdm-run-btn:hover:not(:disabled){transform:translateY(-1px);box-shadow:0 6px 18px rgba(59,130,246,.4);}
.rdm-run-btn:disabled{opacity:.7;}

.rdm-loading-card{display:flex;align-items:flex-start;gap:1.5rem;background:#f8faff;border:1px solid #c7d7fe;border-radius:14px;padding:1.5rem 2rem;margin-bottom:1rem;}
.rdm-loading-card__spinner{flex-shrink:0;padding-top:.25rem;}
.rdm-spinner{width:3rem;height:3rem;border:3px solid #e0e7ff;border-top-color:#4f46e5;border-radius:50%;animation:rdm-spin .8s linear infinite;}
@keyframes rdm-spin{to{transform:rotate(360deg);}}
.rdm-loading-card__title{font-size:1rem;font-weight:700;color:#1e3a5f;margin-bottom:.2rem;}
.rdm-loading-card__sub{font-size:.85rem;color:#6b7280;}
.rdm-loading-steps{display:flex;gap:.75rem 1.25rem;flex-wrap:wrap;}
.rdm-step{display:flex;align-items:center;gap:.4rem;font-size:.8rem;color:#9ca3af;transition:color .3s;}
.rdm-step__dot{width:8px;height:8px;border-radius:50%;background:#d1d5db;flex-shrink:0;transition:background .3s,box-shadow .3s;}
.rdm-step.active .rdm-step__dot{background:#4f46e5;box-shadow:0 0 0 3px rgba(79,70,229,.2);}
.rdm-step.active{color:#4f46e5;font-weight:600;}
.rdm-step.done .rdm-step__dot{background:#22c55e;}
.rdm-step.done{color:#16a34a;}

.rdm-error-card{display:flex;align-items:center;gap:1rem;background:#fff5f5;border:1px solid #fecaca;border-left:4px solid #ef4444;border-radius:10px;padding:1rem 1.2rem;margin-bottom:1rem;}
.rdm-error-card__icon{font-size:1.5rem;color:#ef4444;flex-shrink:0;}
.rdm-error-card__title{font-weight:700;color:#991b1b;font-size:.9rem;}
.rdm-error-card__msg{color:#b91c1c;font-size:.85rem;margin-top:.15rem;}

.rdm-kpi{display:flex;align-items:center;gap:.8rem;background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:.9rem 1.1rem;box-shadow:0 2px 8px rgba(0,0,0,.05);transition:box-shadow .2s,transform .15s;height:100%;}
.rdm-kpi:hover{box-shadow:0 6px 20px rgba(0,0,0,.09);transform:translateY(-2px);}
.rdm-kpi__icon{width:46px;height:46px;flex-shrink:0;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;}
.rdm-kpi__value{font-size:1.65rem;font-weight:800;color:#0f172a;line-height:1;}
.rdm-kpi__label{font-size:.8rem;color:#6b7280;margin-top:.15rem;line-height:1.35;}
.rdm-kpi--blue .rdm-kpi__icon{background:#dbeafe;color:#1d4ed8;}
.rdm-kpi--purple .rdm-kpi__icon{background:#ede9fe;color:#7c3aed;}
.rdm-kpi--green .rdm-kpi__icon{background:#dcfce7;color:#16a34a;}
.rdm-kpi--amber .rdm-kpi__icon{background:#fef3c7;color:#d97706;}
.rdm-kpi--red .rdm-kpi__icon{background:#fee2e2;color:#dc2626;}
.rdm-kpi--orange .rdm-kpi__icon{background:#ffedd5;color:#ea580c;}

.rdm-section-label{display:flex;align-items:center;gap:.75rem;color:#6b7280;font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;}
.rdm-section-label::after{content:'';flex:1;height:1px;background:#e5e7eb;}

.rdm-tab-wrapper{background:#fff;border-radius:14px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.04);}
.rdm-tabs{padding:.5rem .75rem 0;background:#f8fafc;border-bottom:1px solid #e5e7eb;flex-wrap:nowrap;overflow-x:auto;}
.rdm-tab{color:#6b7280 !important;font-weight:500;font-size:.85rem;padding:.6rem 1rem;border-radius:8px 8px 0 0;border:none !important;white-space:nowrap;transition:color .15s,background .15s;}
.rdm-tab:hover{color:#374151 !important;background:#f0f4f8 !important;}
.rdm-tab.active{color:#1d4ed8 !important;font-weight:700;background:#fff !important;border-bottom:2px solid #1d4ed8 !important;}
.rdm-badge{display:inline-block;padding:.2em .55em;border-radius:999px;font-size:.72rem;font-weight:700;vertical-align:middle;}
.rdm-badge--red{background:#fee2e2;color:#dc2626;} .rdm-badge--amber{background:#fef3c7;color:#b45309;}
.rdm-badge--orange{background:#ffedd5;color:#ea580c;} .rdm-badge--green{background:#dcfce7;color:#16a34a;}
.rdm-tab-content{padding:1.25rem;}
.rdm-tab-desc{font-size:.84rem;padding:.65rem 1rem;border-radius:8px;margin-bottom:1rem;display:flex;align-items:flex-start;gap:.5rem;flex-wrap:wrap;}
.rdm-tab-desc--red{background:#fff5f5;color:#991b1b;border:1px solid #fecaca;}
.rdm-tab-desc--amber{background:#fffbeb;color:#92400e;border:1px solid #fde68a;}
.rdm-tab-desc--orange{background:#fff7ed;color:#9a3412;border:1px solid #fed7aa;}
.rdm-tab-desc--green{background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;}

.rdm-table{border-collapse:separate;border-spacing:0;width:100%;}
.rdm-table thead th{background:#f1f5f9;color:#475569;font-size:.74rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;border-bottom:2px solid #e2e8f0;border-top:none;padding:.55rem .75rem;white-space:nowrap;}
.rdm-table tbody td{font-size:.85rem;vertical-align:middle;padding:.45rem .75rem;border-bottom:1px solid #f1f5f9;border-top:none;color:#374151;}
.rdm-table tbody tr:hover td{background:#f8faff;}
.rdm-table tbody tr:last-child td{border-bottom:none;}

.badge-match-nisn{background:#dbeafe;color:#1d4ed8;font-size:.75rem;padding:.22em .6em;border-radius:6px;font-weight:700;}
.badge-match-nis{background:#ede9fe;color:#7c3aed;font-size:.75rem;padding:.22em .6em;border-radius:6px;font-weight:700;}
.badge-data-ok{background:#dcfce7;color:#16a34a;font-size:.78rem;padding:.22em .6em;border-radius:6px;}
.badge-data-no{background:#fee2e2;color:#dc2626;font-size:.78rem;padding:.22em .6em;border-radius:6px;}

.fuzzy-chip{display:inline-flex;align-items:center;gap:.3rem;padding:.28rem .65rem;border-radius:999px;font-size:.78rem;font-weight:600;cursor:pointer;margin:.15rem .1rem;text-decoration:none;border:none;line-height:1.4;transition:opacity .15s,transform .1s;}
.fuzzy-chip:hover{opacity:.82;transform:translateY(-1px);text-decoration:none;}
.fuzzy-chip--high{background:#d1fae5;color:#065f46;} .fuzzy-chip--medium{background:#fef3c7;color:#92400e;} .fuzzy-chip--low{background:#f1f5f9;color:#475569;}
.fuzzy-chip .score-pct{font-size:.7rem;opacity:.8;font-weight:700;margin-left:.1rem;}

.dataTables_wrapper .dataTables_filter input{border:1.5px solid #d1d5db;border-radius:7px;padding:.3rem .6rem;font-size:.83rem;}
.dataTables_wrapper .dataTables_length select{border:1.5px solid #d1d5db;border-radius:7px;font-size:.83rem;}
.dataTables_wrapper .dataTables_info{font-size:.8rem;color:#9ca3af;}
</style>
@endsection

@section('js')
<script src="//cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
<script>
let dtRdmOnly=null,dtFuzzy=null,dtSimansaOnly=null,dtMatched=null;
const loadingSteps=[
    {id:'step1',label:'Mengambil data RDM',delay:0},
    {id:'step2',label:'Pre-match via NIS',delay:3000},
    {id:'step3',label:'Dekripsi nama siswa',delay:8000},
    {id:'step4',label:'Smart fuzzy matching',delay:20000},
    {id:'step5',label:'Menyusun hasil',delay:45000},
];
let stepTimers=[];
function startLoadingAnim(){
    stepTimers.forEach(clearTimeout); stepTimers=[];
    loadingSteps.forEach(s=>$('#'+s.id).removeClass('active done'));
    loadingSteps.forEach((s,i)=>{
        const t=setTimeout(()=>{
            if(i>0) $('#'+loadingSteps[i-1].id).removeClass('active').addClass('done');
            $('#'+s.id).addClass('active');
            $('#loadingTitle').text(s.label+'…');
        },s.delay);
        stepTimers.push(t);
    });
}
function stopLoadingAnim(){ stepTimers.forEach(clearTimeout); stepTimers=[]; }

$(function(){
    $('#btnRunMatching').on('click',runMatching);
});

function runMatching(){
    const tingkatId=$('#selectTingkat').val();
    $('#resultArea').hide(); $('#errorArea').hide(); $('#loadingArea').show();
    $('#btnRunMatching').prop('disabled',true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Memproses…');
    startLoadingAnim();
    $.ajax({
        url:'{{ route("admin.rdm-matching.run") }}',method:'POST',
        data:{tingkat_id:tingkatId,_token:'{{ csrf_token() }}'},timeout:300000,
        success:function(res){
            if(res.status==='success'){
                stopLoadingAnim();
                loadingSteps.forEach(s=>$('#'+s.id).removeClass('active').addClass('done'));
                setTimeout(()=>renderResult(res.data),150);
            } else { showError(res.message||'Terjadi kesalahan.'); }
        },
        error:function(xhr){
            const m=xhr.responseJSON?.message
                ||(xhr.status===0?'Koneksi terputus atau request timeout. Coba lagi.':null)
                ||(xhr.status===504?'Gateway timeout — server tidak merespons tepat waktu.':null)
                ||(xhr.status===500?'Server error (500). Cek log Laravel.':null)
                ||'Gagal (HTTP '+xhr.status+'). Coba lagi.';
            showError(m);
        },
        complete:function(){
            stopLoadingAnim(); $('#loadingArea').hide();
            $('#btnRunMatching').prop('disabled',false).html('<i class="fas fa-play mr-2"></i> Jalankan Matching');
        },
    });
}

function showError(msg){ $('#errorMsg').html(msg); $('#errorArea').show(); $('#loadingArea').hide(); }

function renderResult(data){
    const s=data.stats;
    const matchPct=s.total_rdm>0?Math.round(s.total_matched/s.total_rdm*100):0;
    $('#summaryCards').html(`
        <div class="col-6 col-md-4 col-xl-2 mb-3"><div class="rdm-kpi rdm-kpi--blue">
            <div class="rdm-kpi__icon"><i class="fas fa-database"></i></div>
            <div><div class="rdm-kpi__value">${fmt(s.total_rdm)}</div><div class="rdm-kpi__label">Total RDM<br><small>${data.tahun_rdm||''} · ${data.tingkat_label}</small></div></div>
        </div></div>
        <div class="col-6 col-md-4 col-xl-2 mb-3"><div class="rdm-kpi rdm-kpi--purple">
            <div class="rdm-kpi__icon"><i class="fas fa-users"></i></div>
            <div><div class="rdm-kpi__value">${fmt(s.total_simansa)}</div><div class="rdm-kpi__label">Total SIMANSA<br><small>${data.tingkat_label}</small></div></div>
        </div></div>
        <div class="col-6 col-md-4 col-xl-2 mb-3"><div class="rdm-kpi rdm-kpi--green">
            <div class="rdm-kpi__icon"><i class="fas fa-check-double"></i></div>
            <div><div class="rdm-kpi__value">${fmt(s.total_matched)}</div><div class="rdm-kpi__label">Cocok Pasti<br><small>${matchPct}% dari RDM</small></div></div>
        </div></div>
        <div class="col-6 col-md-4 col-xl-2 mb-3"><div class="rdm-kpi rdm-kpi--amber">
            <div class="rdm-kpi__icon"><i class="fas fa-magic"></i></div>
            <div><div class="rdm-kpi__value">${fmt(s.total_fuzzy)}</div><div class="rdm-kpi__label">Perlu Verifikasi<br><small>nama mirip</small></div></div>
        </div></div>
        <div class="col-6 col-md-4 col-xl-2 mb-3"><div class="rdm-kpi rdm-kpi--red">
            <div class="rdm-kpi__icon"><i class="fas fa-exclamation-circle"></i></div>
            <div><div class="rdm-kpi__value">${fmt(s.total_rdm_only)}</div><div class="rdm-kpi__label">Belum di SIMANSA<br><small>tanpa kandidat</small></div></div>
        </div></div>
        <div class="col-6 col-md-4 col-xl-2 mb-3"><div class="rdm-kpi rdm-kpi--orange">
            <div class="rdm-kpi__icon"><i class="fas fa-user-slash"></i></div>
            <div><div class="rdm-kpi__value">${fmt(s.total_simansa_only)}</div><div class="rdm-kpi__label">Tidak di RDM<br><small>belum diinput</small></div></div>
        </div></div>
    `);

    $('#badgeRdmOnly').text(s.total_rdm_only); $('#badgeFuzzy').text(s.total_fuzzy);
    $('#badgeSimansaOnly').text(s.total_simansa_only); $('#badgeMatched').text(s.total_matched);

    if(dtRdmOnly){dtRdmOnly.destroy();} if(dtFuzzy){dtFuzzy.destroy();}
    if(dtSimansaOnly){dtSimansaOnly.destroy();} if(dtMatched){dtMatched.destroy();}

    // Tab: Belum di SIMANSA
    $('#bodyRdmOnly').html(data.rdm_only.map((r,i)=>`<tr>
        <td class="text-center text-muted">${i+1}</td>
        <td><strong>${esc(r.rdm_nama)}</strong></td>
        <td><code class="text-muted">${esc(r.rdm_nis)}</code></td>
        <td><code class="text-muted">${esc(r.rdm_nisn)}</code></td>
        <td>${esc(r.rdm_tingkat)}</td>
        <td><span class="badge badge-secondary">${esc(r.rdm_kelas)}</span></td>
        <td>${r.rdm_gender==='L'?'<span class="badge-match-nisn">L</span>':'<span style="background:#fce7f3;color:#9d174d;font-size:.75rem;padding:.22em .6em;border-radius:6px;font-weight:700;">P</span>'}</td>
        <td class="text-muted">${esc(r.rdm_tgllahir||'-')}</td>
    </tr>`).join('')||emptyRow(8,'Tidak ada — semua siswa RDM sudah terdeteksi di SIMANSA ??'));

    // Tab: Fuzzy
    $('#bodyFuzzy').html(data.fuzzy_candidates.map((r,i)=>{
        const chips=r.candidates.map(c=>{
            const cls=c.score>=88?'fuzzy-chip--high':(c.score>=72?'fuzzy-chip--medium':'fuzzy-chip--low');
            const icon=c.score>=88?'?':(c.score>=72?'??':'?');
            return `<a class="fuzzy-chip ${cls}" href="/admin/siswa/${c.simansa_id}" target="_blank" title="Kelas:${esc(c.simansa_kelas||'-')} NISN:${esc(c.simansa_nisn||'-')}">${icon} ${esc(c.simansa_nama)} <span class="score-pct">${c.score}%</span></a>`;
        }).join('');
        return `<tr>
            <td class="text-center text-muted">${i+1}</td>
            <td><strong>${esc(r.rdm_nama)}</strong><br><small class="text-muted">NIS: ${esc(r.rdm_nis)}</small></td>
            <td><code class="text-muted">${esc(r.rdm_nisn||'-')}</code></td>
            <td><span class="badge badge-secondary">${esc(r.rdm_tingkat)}</span><br><small>${esc(r.rdm_kelas)}</small></td>
            <td>${chips}</td>
        </tr>`;
    }).join('')||emptyRow(5,'Tidak ada kandidat fuzzy.'));

    // Tab: Tidak di RDM
    $('#bodySimansaOnly').html(data.simansa_only.map((r,i)=>`<tr>
        <td class="text-center text-muted">${i+1}</td>
        <td><strong>${esc(r.simansa_nama)}</strong></td>
        <td><code class="text-muted">${esc(r.simansa_nisn||'-')}</code></td>
        <td><span class="badge badge-secondary">${esc(r.simansa_kelas||'-')}</span></td>
        <td>${badgeDL(r.simansa_data_lengkap)}</td>
        <td class="text-center"><a href="/admin/siswa/${r.simansa_id}" class="btn btn-sm btn-primary" target="_blank"><i class="fas fa-eye"></i></a></td>
    </tr>`).join('')||emptyRow(6,'Tidak ada data.'));

    // Tab: Cocok
    $('#bodyMatched').html(data.matched.map((r,i)=>`<tr>
        <td class="text-center text-muted">${i+1}</td>
        <td>${esc(r.rdm_nama)}</td><td>${esc(r.simansa_nama)}</td>
        <td><code class="text-muted">${esc(r.rdm_nisn||'-')}</code></td>
        <td><code class="text-muted">${esc(r.rdm_nis||'-')}</code></td>
        <td><span class="badge badge-secondary">${esc(r.rdm_kelas)}</span></td>
        <td><span class="badge badge-light">${esc(r.simansa_kelas||'-')}</span></td>
        <td>${r.match_by==='nisn'?'<span class="badge-match-nisn">NISN</span>':'<span class="badge-match-nis">NIS</span>'}</td>
        <td>${badgeDL(r.simansa_data_lengkap)}</td>
        <td class="text-center"><a href="/admin/siswa/${r.simansa_id}" class="btn btn-sm btn-primary" target="_blank"><i class="fas fa-eye"></i></a></td>
    </tr>`).join('')||emptyRow(10,'Tidak ada data cocok.'));

    dtRdmOnly=$('#tableRdmOnly').DataTable({language:dtLang(),order:[[4,'asc'],[5,'asc']],pageLength:25});
    dtFuzzy=$('#tableFuzzy').DataTable({language:dtLang(),order:[[3,'asc']],pageLength:25,columnDefs:[{orderable:false,targets:4}]});
    dtSimansaOnly=$('#tableSimansaOnly').DataTable({language:dtLang(),order:[[3,'asc']],pageLength:25,columnDefs:[{orderable:false,targets:5}]});
    dtMatched=$('#tableMatched').DataTable({language:dtLang(),order:[[5,'asc']],pageLength:25,columnDefs:[{orderable:false,targets:9}]});

    $('#resultArea').show();
    if(s.total_fuzzy>0&&s.total_rdm_only===0){ $('[href="#tabFuzzy"]').tab('show'); }
    $('html,body').animate({scrollTop:$('#resultArea').offset().top-90},400);
}

function badgeDL(ok){ return ok?'<span class="badge-data-ok"><i class="fas fa-check mr-1"></i>Lengkap</span>':'<span class="badge-data-no"><i class="fas fa-times mr-1"></i>Belum</span>'; }
function emptyRow(n,msg){ return `<tr><td colspan="${n}" class="text-center py-4 text-muted"><i class="fas fa-inbox fa-2x d-block mb-2" style="opacity:.3"></i>${msg}</td></tr>`; }
function esc(s){ if(!s&&s!==0)return'-'; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function fmt(n){ return new Intl.NumberFormat('id-ID').format(n); }
function dtLang(){ return{search:'Cari:',lengthMenu:'Tampilkan _MENU_ data',info:'_START_–_END_ dari _TOTAL_ data',infoEmpty:'0 data',paginate:{previous:'‹',next:'›'},zeroRecords:'Tidak ada data.'}; }
</script>
@endsection
