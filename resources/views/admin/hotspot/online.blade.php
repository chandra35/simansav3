@extends('adminlte::page')

@section('title', 'Monitoring Hotspot')

@section('css')
<style>
.hotspot-hero{background:linear-gradient(135deg,#0f766e,#0f4c81);border-radius:18px;color:#fff;padding:1.1rem 1.25rem;margin-bottom:1rem;box-shadow:0 10px 25px rgba(15,76,129,.2)}
.hotspot-hero h1{font-size:1.35rem;font-weight:800;margin:0}.hotspot-hero p{font-size:.82rem;opacity:.86;margin:.25rem 0 0}.hotspot-nav{display:flex;gap:.4rem;flex-wrap:wrap;margin-top:.85rem}.hotspot-nav .btn{border-radius:9px;font-size:.76rem;font-weight:700}
.metric-grid{display:grid;grid-template-columns:repeat(6,minmax(125px,1fr));gap:.7rem;margin-bottom:1rem}.metric-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:.75rem .85rem;box-shadow:0 2px 10px rgba(15,23,42,.05)}.metric-label{font-size:.66rem;color:#64748b;text-transform:uppercase;letter-spacing:.05em;font-weight:700}.metric-value{font-size:1.25rem;font-weight:800;color:#0f172a;margin-top:.15rem}.metric-note{font-size:.67rem;color:#94a3b8}
.hs-panel{background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(15,23,42,.06);margin-bottom:1rem}.hs-panel-head{padding:.75rem 1rem;background:#f8fafc;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;gap:.6rem;flex-wrap:wrap}.hs-panel-title{font-size:.87rem;font-weight:800;color:#1e293b}.hs-table{margin:0;font-size:.78rem}.hs-table th{font-size:.66rem;text-transform:uppercase;letter-spacing:.04em;color:#64748b;background:#f8fafc;border-top:0;white-space:nowrap}.hs-table td{vertical-align:middle}.person{display:flex;align-items:center;gap:.65rem;min-width:220px}.person img{width:42px;height:42px;border-radius:12px;object-fit:cover;border:2px solid #e2e8f0}.person-name{font-weight:750;color:#1e293b}.person-meta{font-size:.68rem;color:#64748b}.live-dot{width:8px;height:8px;border-radius:50%;background:#22c55e;display:inline-block;box-shadow:0 0 0 4px rgba(34,197,94,.12);margin-right:.4rem}.detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.6rem}.detail-item{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:.55rem .65rem}.detail-label{font-size:.62rem;color:#64748b;text-transform:uppercase;font-weight:700}.detail-value{font-size:.8rem;color:#0f172a;font-weight:650;word-break:break-word}.profile-photo{width:92px;height:92px;border-radius:18px;object-fit:cover;border:3px solid #e2e8f0}.empty-state{text-align:center;padding:2.5rem;color:#64748b}.refresh-note{font-size:.68rem;color:#64748b}
@media(max-width:1100px){.metric-grid{grid-template-columns:repeat(3,1fr)}}@media(max-width:767px){.metric-grid{grid-template-columns:repeat(2,1fr)}.detail-grid{grid-template-columns:1fr}.hotspot-hero{border-radius:14px}.person{min-width:190px}}
</style>
@endsection

@section('content_header')
<div class="hotspot-hero">
    <div class="d-flex justify-content-between align-items-start flex-wrap">
        <div>
            <h1><i class="fas fa-satellite-dish mr-2"></i>Monitoring Hotspot</h1>
            <p>Sesi online, pemakaian data, perangkat, identitas pengguna, dan histori koneksi FreeRADIUS.</p>
        </div>
        <span class="badge badge-{{ $radiusConnected ? 'success' : 'danger' }} px-3 py-2">
            {{ $radiusConnected ? 'FreeRADIUS terhubung' : 'FreeRADIUS offline' }}
        </span>
    </div>
    <div class="hotspot-nav">
        <a href="{{ route('admin.hotspot.index') }}" class="btn btn-light"><i class="fas fa-users mr-1"></i>Akun</a>
        <a href="{{ route('admin.hotspot.online') }}" class="btn btn-warning"><i class="fas fa-circle mr-1"></i>Monitoring</a>
        <a href="{{ route('admin.hotspot.auth-logs') }}" class="btn btn-outline-light"><i class="fas fa-clipboard-list mr-1"></i>Log Auth</a>
        @can('manage-hotspot')<a href="{{ route('admin.hotspot.settings') }}" class="btn btn-outline-light"><i class="fas fa-cogs mr-1"></i>Setting</a>@endcan
    </div>
</div>
@endsection

@section('content')
<div class="metric-grid">
    <div class="metric-card"><div class="metric-label">Online sekarang</div><div class="metric-value text-success" id="metricOnline">-</div><div class="metric-note">sesi aktif</div></div>
    <div class="metric-card"><div class="metric-label">Guru/GTK</div><div class="metric-value text-primary" id="metricGuru">-</div><div class="metric-note">sedang online</div></div>
    <div class="metric-card"><div class="metric-label">Siswa</div><div class="metric-value text-info" id="metricSiswa">-</div><div class="metric-note">sedang online</div></div>
    <div class="metric-card"><div class="metric-label">User unik hari ini</div><div class="metric-value" id="metricUnique">-</div><div class="metric-note">berdasarkan accounting</div></div>
    <div class="metric-card"><div class="metric-label">Download hari ini</div><div class="metric-value" id="metricDownload">-</div><div class="metric-note">dari router ke user</div></div>
    <div class="metric-card"><div class="metric-label">Upload hari ini</div><div class="metric-value" id="metricUpload">-</div><div class="metric-note">dari user ke router</div></div>
</div>

<div class="hs-panel">
    <div class="hs-panel-head">
        <div class="hs-panel-title"><span class="live-dot"></span>User sedang online <span class="badge badge-success ml-1" id="onlineCount">0</span></div>
        <div class="d-flex flex-wrap" style="gap:.4rem">
            <input id="searchOnline" class="form-control form-control-sm" style="width:220px" placeholder="Nama, username, IP, MAC, rombel">
            <select id="roleOnline" class="form-control form-control-sm" style="width:125px"><option value="">Semua role</option><option value="guru">Guru</option><option value="siswa">Siswa</option><option value="tamu">Tamu</option></select>
            <button id="refreshOnline" class="btn btn-sm btn-outline-primary"><i class="fas fa-sync mr-1"></i>Refresh</button>
        </div>
    </div>
    <div class="table-responsive" id="onlineWrap"><div class="empty-state"><i class="fas fa-spinner fa-spin mr-1"></i>Memuat sesi aktif...</div></div>
    <div class="px-3 py-2 border-top refresh-note">Pembaruan otomatis setiap 30 detik. Terakhir diperbarui: <span id="lastRefresh">-</span></div>
</div>

<div class="hs-panel">
    <div class="hs-panel-head"><div class="hs-panel-title"><i class="fas fa-history text-secondary mr-1"></i>15 sesi terakhir berakhir</div><a href="{{ route('admin.hotspot.auth-logs') }}" class="btn btn-sm btn-outline-secondary">Lihat log autentikasi</a></div>
    <div class="table-responsive" id="recentWrap"><div class="empty-state">Memuat histori sesi...</div></div>
</div>

<div class="modal fade" id="sessionDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content border-0 shadow" style="border-radius:18px;overflow:hidden">
        <div class="modal-header bg-primary text-white"><h5 class="modal-title"><i class="fas fa-user-circle mr-2"></i>Detail User Online</h5><button class="close text-white" data-dismiss="modal">&times;</button></div>
        <div class="modal-body" id="sessionDetailBody"></div>
    </div></div>
</div>
@endsection

@section('js')
<script>
const ONLINE_URL = @json(route('admin.hotspot.online-users'));
let sessions = [];
const esc = value => $('<div>').text(value == null ? '' : String(value)).html();
const attr = value => esc(value).replace(/`/g, '&#96;');
function bytes(value){value=Number(value||0);const unit=['B','KB','MB','GB','TB'];let i=0;while(value>=1024&&i<unit.length-1){value/=1024;i++;}return `${value.toFixed(i?1:0)} ${unit[i]}`;}
function duration(value){value=Number(value||0);const h=Math.floor(value/3600),m=Math.floor((value%3600)/60),s=value%60;return `${h?h+'j ':''}${m?m+'m ':''}${s}d`;}
function dateTime(value){return value?new Date(value).toLocaleString('id-ID',{dateStyle:'medium',timeStyle:'short'}):'-';}

function loadOnline(){
    $('#refreshOnline').prop('disabled',true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Memuat');
    $.get(ONLINE_URL).done(response=>{
        if(!response.success) throw new Error(response.error||'Gagal memuat data');
        sessions=response.sessions||[];
        const summary=response.summary||{};
        $('#metricOnline,#onlineCount').text(sessions.length);
        $('#metricGuru').text(sessions.filter(x=>x.role==='guru').length);
        $('#metricSiswa').text(sessions.filter(x=>x.role==='siswa').length);
        $('#metricUnique').text(summary.unique_today||0);
        $('#metricDownload').text(bytes(summary.download_today));
        $('#metricUpload').text(bytes(summary.upload_today));
        $('#lastRefresh').text(new Date().toLocaleTimeString('id-ID'));
        renderOnline();renderRecent(response.recent_sessions||[]);
    }).fail(xhr=>$('#onlineWrap').html(`<div class="empty-state text-danger"><i class="fas fa-exclamation-triangle mr-1"></i>${esc(xhr.responseJSON?.error||'FreeRADIUS tidak dapat dihubungi.')}</div>`))
      .always(()=>$('#refreshOnline').prop('disabled',false).html('<i class="fas fa-sync mr-1"></i>Refresh'));
}
function renderOnline(){
    const search=($('#searchOnline').val()||'').toLowerCase(),role=$('#roleOnline').val();
    const rows=sessions.map((s,index)=>({s,index})).filter(({s})=>(!role||s.role===role)&&(!search||[s.display_name,s.username,s.framed_ip,s.mac,s.kelas].join(' ').toLowerCase().includes(search)));
    if(!rows.length){$('#onlineWrap').html('<div class="empty-state"><i class="fas fa-wifi fa-2x mb-2 d-block"></i>Tidak ada sesi yang cocok.</div>');return;}
    $('#onlineWrap').html(`<table class="table hs-table table-hover"><thead><tr><th>User</th><th>Role/Rombel</th><th>Perangkat</th><th>Durasi</th><th>Pemakaian</th><th>Profile</th><th></th></tr></thead><tbody>${rows.map(({s,index})=>`<tr>
        <td><div class="person"><img src="${attr(s.photo_url)}" alt="Foto"><div><div class="person-name">${esc(s.display_name)}</div><div class="person-meta"><code>${esc(s.username)}</code></div></div></div></td>
        <td><span class="badge badge-${s.role==='guru'?'primary':s.role==='siswa'?'info':'warning'}">${esc(s.role)}</span>${s.kelas?`<div class="person-meta mt-1">${esc(s.kelas)}</div>`:''}</td>
        <td><strong>${esc(s.framed_ip||'-')}</strong><div class="person-meta">${esc(s.mac||'-')}</div></td>
        <td>${duration(s.session_time)}<div class="person-meta">${dateTime(s.started_at)}</div></td>
        <td><span class="text-success"><i class="fas fa-arrow-down"></i> ${bytes(s.bytes_out)}</span><br><span class="text-primary"><i class="fas fa-arrow-up"></i> ${bytes(s.bytes_in)}</span></td>
        <td>${esc(s.profile||'-')}<div class="person-meta">${esc(s.queue_name||'')}</div></td>
        <td><button class="btn btn-sm btn-outline-primary" onclick="showSession(${index})"><i class="fas fa-eye"></i></button></td>
    </tr>`).join('')}</tbody></table>`);
}
function renderRecent(rows){
    if(!rows.length){$('#recentWrap').html('<div class="empty-state">Belum ada sesi selesai.</div>');return;}
    $('#recentWrap').html(`<table class="table hs-table"><thead><tr><th>User</th><th>IP / MAC</th><th>Durasi</th><th>Berakhir</th><th>Penyebab</th></tr></thead><tbody>${rows.map(s=>`<tr><td><strong>${esc(s.display_name)}</strong><div class="person-meta">${esc(s.username)}</div></td><td>${esc(s.framed_ip||'-')}<div class="person-meta">${esc(s.mac||'-')}</div></td><td>${duration(s.session_time)}</td><td>${dateTime(s.stopped_at)}</td><td><span class="badge badge-light border">${esc(s.terminate_cause)}</span></td></tr>`).join('')}</tbody></table>`);
}
function showSession(index){
    const s=sessions[index],id=s.identity||{};
    const link=s.detail_url?`<a href="${attr(s.detail_url)}" class="btn btn-primary btn-sm"><i class="fas fa-id-card mr-1"></i>Buka detail ${s.role==='siswa'?'siswa':'GTK'}</a>`:'';
    const classLink=s.kelas_url?`<a href="${attr(s.kelas_url)}" class="btn btn-outline-primary btn-sm"><i class="fas fa-users mr-1"></i>Buka rombel ${esc(s.kelas)}</a>`:'';
    $('#sessionDetailBody').html(`<div class="d-flex flex-wrap align-items-center mb-3" style="gap:1rem"><img class="profile-photo" src="${attr(s.photo_url)}"><div><h4 class="font-weight-bold mb-1">${esc(s.display_name)}</h4><div class="text-muted"><code>${esc(s.username)}</code> &middot; ${esc(s.role)}</div><div class="mt-2">${link} ${classLink}</div></div></div>
    <div class="detail-grid">
      <div class="detail-item"><div class="detail-label">${esc(id.label||'Identitas')}</div><div class="detail-value">${esc(id.value||s.username)}</div></div>
      <div class="detail-item"><div class="detail-label">${esc(id.secondary_label||'Status')}</div><div class="detail-value">${esc(id.secondary_value||id.status||'-')}</div></div>
      <div class="detail-item"><div class="detail-label">Rombel</div><div class="detail-value">${esc(s.kelas||'-')}</div></div>
      <div class="detail-item"><div class="detail-label">Profile RADIUS</div><div class="detail-value">${esc(s.profile||'-')}</div></div>
      <div class="detail-item"><div class="detail-label">IP / MAC</div><div class="detail-value">${esc(s.framed_ip||'-')} / ${esc(s.mac||'-')}</div></div>
      <div class="detail-item"><div class="detail-label">NAS / Port</div><div class="detail-value">${esc(s.nas_ip||'-')} / ${esc(s.nas_port||'-')}</div></div>
      <div class="detail-item"><div class="detail-label">Durasi</div><div class="detail-value">${duration(s.session_time)} sejak ${dateTime(s.started_at)}</div></div>
      <div class="detail-item"><div class="detail-label">Dynamic Simple Queue</div><div class="detail-value">${esc(s.queue_name||'-')}</div></div>
    </div>`);
    $('#sessionDetailModal').modal('show');
}
$('#searchOnline').on('input',renderOnline);$('#roleOnline').on('change',renderOnline);$('#refreshOnline').on('click',loadOnline);
loadOnline();setInterval(loadOnline,30000);
</script>
@endsection
