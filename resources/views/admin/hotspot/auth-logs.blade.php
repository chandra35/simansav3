@extends('adminlte::page')

@section('title', 'Log Autentikasi Hotspot')

@section('css')
<style>
.auth-hero{background:linear-gradient(135deg,#4338ca,#0f766e);border-radius:18px;color:#fff;padding:1.1rem 1.25rem;margin-bottom:1rem;box-shadow:0 10px 25px rgba(67,56,202,.18)}.auth-hero h1{font-size:1.35rem;font-weight:800;margin:0}.auth-hero p{font-size:.82rem;opacity:.86;margin:.25rem 0 0}.auth-nav{display:flex;gap:.4rem;flex-wrap:wrap;margin-top:.85rem}.auth-nav .btn{border-radius:9px;font-size:.76rem;font-weight:700}
.auth-metrics{display:grid;grid-template-columns:repeat(4,1fr);gap:.7rem;margin-bottom:1rem}.auth-metric{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:.8rem 1rem}.auth-metric small{font-size:.66rem;text-transform:uppercase;color:#64748b;font-weight:700;letter-spacing:.05em}.auth-metric strong{display:block;font-size:1.4rem;color:#0f172a}.auth-panel{background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(15,23,42,.06)}.auth-toolbar{padding:.75rem 1rem;background:#f8fafc;border-bottom:1px solid #e2e8f0;display:flex;gap:.5rem;align-items:center;flex-wrap:wrap}.auth-table{margin:0;font-size:.78rem}.auth-table th{font-size:.65rem;text-transform:uppercase;letter-spacing:.05em;color:#64748b;background:#f8fafc;border-top:0}.auth-table td{vertical-align:middle}.auth-user{display:flex;align-items:center;gap:.6rem;min-width:220px}.auth-user img{width:38px;height:38px;border-radius:10px;object-fit:cover;border:2px solid #e2e8f0}.auth-name{font-weight:750;color:#1e293b}.auth-meta{font-size:.67rem;color:#64748b}.reason{max-width:420px;white-space:normal;line-height:1.35}.empty{text-align:center;padding:3rem;color:#64748b}.pagination-wrap{padding:.65rem 1rem;border-top:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;gap:.5rem;flex-wrap:wrap}
@media(max-width:767px){.auth-metrics{grid-template-columns:repeat(2,1fr)}.auth-hero{border-radius:14px}.auth-toolbar>*{flex:1 1 145px}}
</style>
@endsection

@section('content_header')
<div class="auth-hero">
    <div class="d-flex justify-content-between align-items-start flex-wrap">
        <div><h1><i class="fas fa-clipboard-check mr-2"></i>Log Autentikasi Hotspot</h1><p>Login berhasil, password salah, akun nonaktif, username tidak dikenal, dan respons lain dari FreeRADIUS.</p></div>
        <span class="badge badge-{{ $radiusConnected ? 'success' : 'danger' }} px-3 py-2">{{ $radiusConnected ? 'FreeRADIUS terhubung' : 'FreeRADIUS offline' }}</span>
    </div>
    <div class="auth-nav">
        <a href="{{ route('admin.hotspot.index') }}" class="btn btn-light"><i class="fas fa-users mr-1"></i>Akun</a>
        <a href="{{ route('admin.hotspot.online') }}" class="btn btn-outline-light"><i class="fas fa-satellite-dish mr-1"></i>Monitoring</a>
        <a href="{{ route('admin.hotspot.auth-logs') }}" class="btn btn-warning"><i class="fas fa-clipboard-list mr-1"></i>Log Auth</a>
        <a href="{{ route('admin.hotspot.profiles.page') }}" class="btn btn-outline-light"><i class="fas fa-sliders-h mr-1"></i>Profile RADIUS</a>
        @if($radiusDashboardUrl)<a href="{{ $radiusDashboardUrl }}" target="_blank" rel="noopener" class="btn btn-outline-light"><i class="fas fa-external-link-alt mr-1"></i>Dashboard FreeRADIUS</a>@endif
    </div>
</div>
@endsection

@section('content')
<div class="auth-metrics">
    <div class="auth-metric"><small>Total percobaan</small><strong id="sumTotal">-</strong></div>
    <div class="auth-metric"><small>Login berhasil</small><strong class="text-success" id="sumAccepted">-</strong></div>
    <div class="auth-metric"><small>Login ditolak</small><strong class="text-danger" id="sumRejected">-</strong></div>
    <div class="auth-metric"><small>Respons lainnya</small><strong class="text-warning" id="sumOther">-</strong></div>
</div>

<div class="auth-panel">
    <div class="auth-toolbar">
        <input type="date" id="filterDate" class="form-control form-control-sm" value="{{ now()->toDateString() }}" style="max-width:155px">
        <select id="filterResult" class="form-control form-control-sm" style="max-width:165px"><option value="">Semua hasil</option><option value="success">Berhasil</option><option value="reject">Ditolak</option><option value="other">Lainnya</option></select>
        <input id="filterSearch" class="form-control form-control-sm" placeholder="Cari username" style="max-width:220px">
        <select id="filterPerPage" class="form-control form-control-sm" style="max-width:110px"><option value="25">25/baris</option><option value="50">50/baris</option><option value="100">100/baris</option></select>
        <button id="btnLoadLogs" class="btn btn-sm btn-primary"><i class="fas fa-search mr-1"></i>Tampilkan</button>
        <span class="small text-muted ml-auto"><i class="fas fa-shield-alt mr-1"></i>Password percobaan tidak pernah ditampilkan.</span>
    </div>
    <div class="table-responsive" id="logWrap"><div class="empty"><i class="fas fa-spinner fa-spin mr-1"></i>Memuat log...</div></div>
    <div class="pagination-wrap"><span class="small text-muted" id="pageInfo">-</span><div><button id="prevPage" class="btn btn-sm btn-outline-secondary"><i class="fas fa-chevron-left"></i></button><button id="nextPage" class="btn btn-sm btn-outline-secondary ml-1"><i class="fas fa-chevron-right"></i></button></div></div>
</div>
@endsection

@section('js')
<script>
const LOG_URL=@json(route('admin.hotspot.auth-logs.data'));let currentPage=1,lastPage=1;
const esc=value=>$('<div>').text(value==null?'':String(value)).html();
function dateTime(value){return value?new Date(value).toLocaleString('id-ID',{dateStyle:'medium',timeStyle:'medium'}):'-';}
function loadLogs(page=1){
    currentPage=page;$('#btnLoadLogs').prop('disabled',true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Memuat');
    $.get(LOG_URL,{page,date:$('#filterDate').val(),result:$('#filterResult').val(),search:$('#filterSearch').val(),per_page:$('#filterPerPage').val()}).done(r=>{
        $('#sumTotal').text(r.summary.total);$('#sumAccepted').text(r.summary.accepted);$('#sumRejected').text(r.summary.rejected);$('#sumOther').text(r.summary.other);
        lastPage=r.pagination.last_page||1;currentPage=r.pagination.current_page||1;$('#pageInfo').text(`Halaman ${currentPage} dari ${lastPage} · ${r.pagination.total} log`);$('#prevPage').prop('disabled',currentPage<=1);$('#nextPage').prop('disabled',currentPage>=lastPage);renderLogs(r.logs||[]);
    }).fail(xhr=>$('#logWrap').html(`<div class="empty text-danger"><i class="fas fa-exclamation-triangle mr-1"></i>${esc(xhr.responseJSON?.message||'Log tidak dapat dimuat.')}</div>`))
      .always(()=>$('#btnLoadLogs').prop('disabled',false).html('<i class="fas fa-search mr-1"></i>Tampilkan'));
}
function renderLogs(logs){
    if(!logs.length){$('#logWrap').html('<div class="empty"><i class="fas fa-inbox fa-2x d-block mb-2"></i>Tidak ada log pada filter ini.</div>');return;}
    $('#logWrap').html(`<table class="table auth-table table-hover"><thead><tr><th>Waktu</th><th>User</th><th>Hasil</th><th>Penjelasan</th><th></th></tr></thead><tbody>${logs.map(log=>{const success=log.status==='success',other=log.status==='other',badge=success?'success':other?'warning':'danger',label=success?'Berhasil':other?'Lainnya':'Ditolak';return `<tr>
      <td style="white-space:nowrap">${dateTime(log.authdate)}</td>
      <td><div class="auth-user"><img src="${esc(log.photo_url)}" alt="Foto"><div><div class="auth-name">${esc(log.display_name)}</div><div class="auth-meta"><code>${esc(log.username)}</code>${log.kelas?' · '+esc(log.kelas):''}</div></div></div></td>
      <td><span class="badge badge-${badge}">${label}</span><div class="auth-meta mt-1">${esc(log.reply)}</div></td>
      <td><div class="reason">${esc(log.reason)}</div></td>
      <td>${log.detail_url?`<a class="btn btn-sm btn-outline-primary" href="${esc(log.detail_url)}"><i class="fas fa-id-card"></i></a>`:''}</td>
    </tr>`;}).join('')}</tbody></table>`);
}
$('#btnLoadLogs').on('click',()=>loadLogs(1));$('#prevPage').on('click',()=>currentPage>1&&loadLogs(currentPage-1));$('#nextPage').on('click',()=>currentPage<lastPage&&loadLogs(currentPage+1));$('#filterResult,#filterDate,#filterPerPage').on('change',()=>loadLogs(1));let timer;$('#filterSearch').on('input',()=>{clearTimeout(timer);timer=setTimeout(()=>loadLogs(1),350)});loadLogs();
</script>
@endsection
