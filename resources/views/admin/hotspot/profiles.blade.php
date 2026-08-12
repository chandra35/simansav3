@extends('adminlte::page')

@section('title', 'Profile RADIUS Hotspot')

@section('css')
<style>
.profile-hero{background:linear-gradient(135deg,#1d4ed8,#4338ca);border-radius:18px;color:#fff;padding:1.1rem 1.25rem;margin-bottom:1rem;box-shadow:0 10px 25px rgba(29,78,216,.2)}.profile-hero h1{font-size:1.35rem;font-weight:800;margin:0}.profile-hero p{font-size:.82rem;opacity:.86;margin:.25rem 0 0}.profile-nav{display:flex;gap:.4rem;flex-wrap:wrap;margin-top:.85rem}.profile-nav .btn{border-radius:9px;font-size:.76rem;font-weight:700}
.profile-summary{display:grid;grid-template-columns:repeat(4,1fr);gap:.7rem;margin-bottom:1rem}.summary-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:.8rem 1rem}.summary-card small{font-size:.66rem;text-transform:uppercase;color:#64748b;font-weight:700}.summary-card strong{font-size:1.35rem;display:block;color:#0f172a}.profile-grid{display:grid;grid-template-columns:repeat(3,minmax(260px,1fr));gap:.85rem}.profile-card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(15,23,42,.06)}.profile-card-head{padding:.85rem 1rem;border-bottom:1px solid #e2e8f0;background:#f8fafc}.profile-card-title{font-weight:800;color:#1e293b}.profile-card-code{font-family:monospace;font-size:.7rem;color:#64748b}.profile-card-body{padding:.85rem 1rem}.attribute-list{display:grid;grid-template-columns:1fr 1fr;gap:.45rem}.attribute{background:#f8fafc;border:1px solid #e2e8f0;border-radius:9px;padding:.45rem .55rem}.attribute small{display:block;color:#64748b;font-size:.6rem;text-transform:uppercase;font-weight:700}.attribute span{font-size:.76rem;font-weight:650;color:#1e293b;word-break:break-word}.profile-actions{padding:.7rem 1rem;border-top:1px solid #e2e8f0;display:flex;gap:.35rem;flex-wrap:wrap}.sync-state{font-size:.65rem;padding:.24rem .5rem;border-radius:20px;font-weight:750}.sync-state.synced{background:#dcfce7;color:#15803d}.sync-state.drift{background:#fef3c7;color:#a16207}.sync-state.missing,.sync-state.offline{background:#fee2e2;color:#b91c1c}.flow-note{background:#eff6ff;border:1px solid #bfdbfe;border-radius:13px;padding:.8rem 1rem;margin-bottom:1rem;font-size:.78rem;color:#1e3a8a}
@media(max-width:1100px){.profile-grid{grid-template-columns:repeat(2,1fr)}}@media(max-width:767px){.profile-grid{grid-template-columns:1fr}.profile-summary{grid-template-columns:repeat(2,1fr)}.attribute-list{grid-template-columns:1fr}}
</style>
@endsection

@section('content_header')
<div class="profile-hero">
    <div class="d-flex justify-content-between align-items-start flex-wrap">
        <div><h1><i class="fas fa-sliders-h mr-2"></i>Profile RADIUS</h1><p>Kelola group FreeRADIUS yang mengirim profile MikroTik, bandwidth, pool, timeout, dan batas sesi.</p></div>
        <span class="badge badge-{{ $radiusConnected ? 'success' : 'danger' }} px-3 py-2">{{ $radiusConnected ? 'FreeRADIUS terhubung' : 'FreeRADIUS offline' }}</span>
    </div>
    <div class="profile-nav">
        <a href="{{ route('admin.hotspot.index') }}" class="btn btn-light"><i class="fas fa-users mr-1"></i>Akun</a>
        <a href="{{ route('admin.hotspot.online') }}" class="btn btn-outline-light"><i class="fas fa-satellite-dish mr-1"></i>Monitoring</a>
        <a href="{{ route('admin.hotspot.auth-logs') }}" class="btn btn-outline-light"><i class="fas fa-clipboard-list mr-1"></i>Log Auth</a>
        <a href="{{ route('admin.hotspot.profiles.page') }}" class="btn btn-warning"><i class="fas fa-sliders-h mr-1"></i>Profile RADIUS</a>
        @if($radiusDashboardUrl)<a href="{{ $radiusDashboardUrl }}" target="_blank" rel="noopener" class="btn btn-outline-light"><i class="fas fa-external-link-alt mr-1"></i>Dashboard FreeRADIUS</a>@endif
    </div>
</div>
@endsection

@section('content')
@php
    $syncedCount = collect($radiusState)->where('status', 'synced')->count();
    $driftCount = collect($radiusState)->whereIn('status', ['drift', 'missing'])->count();
@endphp
<div class="profile-summary">
    <div class="summary-card"><small>Total profile</small><strong>{{ $profiles->count() }}</strong></div>
    <div class="summary-card"><small>Sinkron</small><strong class="text-success">{{ $syncedCount }}</strong></div>
    <div class="summary-card"><small>Perlu sinkronisasi</small><strong class="text-warning">{{ $driftCount }}</strong></div>
    <div class="summary-card"><small>User terikat</small><strong class="text-primary">{{ $profiles->sum('users_count') }}</strong></div>
</div>

<div class="flow-note"><i class="fas fa-info-circle mr-1"></i><strong>Alur profile:</strong> SIMANSA menulis atribut ke group FreeRADIUS, lalu atribut <code>Mikrotik-Group</code>, <code>Mikrotik-Rate-Limit</code>, pool, dan timeout dikirim ke MikroTik saat user login. Tidak ada perubahan routing atau queue statis.</div>

@can('manage-hotspot')
<div class="d-flex justify-content-end mb-3" style="gap:.4rem"><button id="syncAllProfiles" class="btn btn-outline-primary btn-sm"><i class="fas fa-sync mr-1"></i>Sync semua</button><button id="addProfile" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1"></i>Tambah profile</button></div>
@endcan

<div class="profile-grid">
@forelse($profiles as $profile)
    @php $state = $radiusState[$profile->id] ?? ['status' => 'offline', 'actual' => [], 'expected' => []]; @endphp
    <div class="profile-card">
        <div class="profile-card-head d-flex justify-content-between align-items-start">
            <div><div class="profile-card-title">{{ $profile->name }}</div><div class="profile-card-code">{{ $profile->code }} · {{ strtoupper($profile->role ?: 'CUSTOM') }}</div></div>
            <span class="sync-state {{ $state['status'] }}">{{ strtoupper($state['status']) }}</span>
        </div>
        <div class="profile-card-body">
            <div class="attribute-list">
                <div class="attribute"><small>MikroTik Group</small><span>{{ $profile->mikrotik_group ?: '-' }}</span></div>
                <div class="attribute"><small>Rate Limit</small><span>{{ $profile->rate_limit ?: '-' }}</span></div>
                <div class="attribute"><small>Framed Pool</small><span>{{ $profile->framed_pool ?: '-' }}</span></div>
                <div class="attribute"><small>Session Timeout</small><span>{{ $profile->session_timeout ? $profile->session_timeout.' detik' : '-' }}</span></div>
                <div class="attribute"><small>Idle Timeout</small><span>{{ $profile->idle_timeout ? $profile->idle_timeout.' detik' : '-' }}</span></div>
                <div class="attribute"><small>Simultaneous Use</small><span>{{ $profile->simultaneous_use ?: '-' }}</span></div>
            </div>
            <div class="small text-muted mt-3">{{ $profile->description ?: 'Belum ada deskripsi.' }}</div>
            <div class="small mt-2"><i class="fas fa-users mr-1 text-primary"></i>{{ $profile->users_count }} user langsung · Priority {{ $profile->priority }} @if($profile->is_default) · <strong>Default</strong>@endif</div>
            @if($state['status'] === 'drift')<div class="alert alert-warning py-2 px-2 small mt-2 mb-0"><i class="fas fa-exclamation-triangle mr-1"></i>Atribut di FreeRADIUS berbeda dari SIMANSA. Jalankan Sync.</div>@endif
        </div>
        @can('manage-hotspot')
        <div class="profile-actions"><button class="btn btn-sm btn-outline-primary edit-profile" data-id="{{ $profile->id }}"><i class="fas fa-edit mr-1"></i>Edit</button><button class="btn btn-sm btn-outline-success sync-profile" data-id="{{ $profile->id }}"><i class="fas fa-sync mr-1"></i>Sync</button><button class="btn btn-sm btn-outline-danger delete-profile ml-auto" data-id="{{ $profile->id }}"><i class="fas fa-trash"></i></button></div>
        @endcan
    </div>
@empty
    <div class="alert alert-light border">Belum ada profile RADIUS.</div>
@endforelse
</div>

@can('manage-hotspot')
<div class="modal fade" id="profileModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content border-0 shadow" style="border-radius:18px;overflow:hidden">
    <div class="modal-header bg-primary text-white"><h5 class="modal-title">Profile RADIUS</h5><button class="close text-white" data-dismiss="modal">&times;</button></div>
    <div class="modal-body"><form id="profileForm"><input type="hidden" id="profileId"><div class="row">
        <div class="col-md-7 form-group"><label>Nama profile</label><input id="profileName" class="form-control" required></div><div class="col-md-5 form-group"><label>Kode group RADIUS</label><input id="profileCode" class="form-control" required></div>
        <div class="col-md-4 form-group"><label>Role</label><select id="profileRole" class="form-control"><option value="">Custom</option><option value="guru">Guru</option><option value="siswa">Siswa</option><option value="tamu">Tamu</option></select></div><div class="col-md-4 form-group"><label>MikroTik Group</label><input id="profileMikrotikGroup" class="form-control" placeholder="profile-siswa"></div><div class="col-md-4 form-group"><label>Priority</label><input id="profilePriority" type="number" min="1" class="form-control" value="1"></div>
        <div class="col-md-4 form-group"><label>Rate Limit</label><input id="profileRate" class="form-control" placeholder="5M/5M"></div><div class="col-md-4 form-group"><label>Framed Pool</label><input id="profilePool" class="form-control" placeholder="pool-siswa"></div><div class="col-md-4 form-group"><label>Address List</label><input id="profileAddress" class="form-control"></div>
        <div class="col-md-4 form-group"><label>Session Timeout (detik)</label><input id="profileSession" type="number" min="60" class="form-control"></div><div class="col-md-4 form-group"><label>Idle Timeout (detik)</label><input id="profileIdle" type="number" min="60" class="form-control"></div><div class="col-md-4 form-group"><label>Simultaneous Use</label><input id="profileSimultaneous" type="number" min="1" class="form-control"></div>
        <div class="col-12 form-group"><label>Deskripsi</label><textarea id="profileDescription" class="form-control" rows="2"></textarea></div>
        <div class="col-md-6"><div class="custom-control custom-checkbox"><input type="checkbox" id="profileDefault" class="custom-control-input"><label for="profileDefault" class="custom-control-label">Jadikan default role</label></div></div><div class="col-md-6"><div class="custom-control custom-checkbox"><input type="checkbox" id="profileActive" class="custom-control-input" checked><label for="profileActive" class="custom-control-label">Profile aktif</label></div></div>
    </div></form></div>
    <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Batal</button><button id="saveProfile" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Simpan & Sync</button></div>
</div></div></div>
@endcan
@endsection

@section('js')
<script>
const PROFILE_DATA=@json($profiles->keyBy('id'));
const ROUTES={store:@json(route('admin.hotspot.profiles.store')),syncAll:@json(route('admin.hotspot.profiles.sync-all')),base:@json(url('admin/hotspot/profiles'))};
function resetForm(){document.getElementById('profileForm').reset();$('#profileId').val('');$('#profilePriority').val(1);$('#profileActive').prop('checked',true)}
function formData(){return{name:$('#profileName').val(),code:$('#profileCode').val(),role:$('#profileRole').val(),mikrotik_group:$('#profileMikrotikGroup').val(),priority:$('#profilePriority').val(),rate_limit:$('#profileRate').val(),framed_pool:$('#profilePool').val(),address_list:$('#profileAddress').val(),session_timeout:$('#profileSession').val(),idle_timeout:$('#profileIdle').val(),simultaneous_use:$('#profileSimultaneous').val(),description:$('#profileDescription').val(),is_default:$('#profileDefault').is(':checked')?1:0,is_active:$('#profileActive').is(':checked')?1:0,_token:@json(csrf_token())}}
$('#addProfile').on('click',()=>{resetForm();$('#profileModal').modal('show')});
$('.edit-profile').on('click',function(){const p=PROFILE_DATA[$(this).data('id')];resetForm();$('#profileId').val(p.id);$('#profileName').val(p.name);$('#profileCode').val(p.code);$('#profileRole').val(p.role||'');$('#profileMikrotikGroup').val(p.mikrotik_group||'');$('#profilePriority').val(p.priority||1);$('#profileRate').val(p.rate_limit||'');$('#profilePool').val(p.framed_pool||'');$('#profileAddress').val(p.address_list||'');$('#profileSession').val(p.session_timeout||'');$('#profileIdle').val(p.idle_timeout||'');$('#profileSimultaneous').val(p.simultaneous_use||'');$('#profileDescription').val(p.description||'');$('#profileDefault').prop('checked',!!p.is_default);$('#profileActive').prop('checked',!!p.is_active);$('#profileModal').modal('show')});
$('#saveProfile').on('click',function(){const id=$('#profileId').val(),btn=$(this);btn.prop('disabled',true);$.ajax({url:id?`${ROUTES.base}/${id}`:ROUTES.store,method:id?'PUT':'POST',data:formData()}).done(r=>{toastr.success(r.message);location.reload()}).fail(xhr=>toastr.error(xhr.responseJSON?.message||Object.values(xhr.responseJSON?.errors||{}).flat()[0]||'Gagal menyimpan profile')).always(()=>btn.prop('disabled',false))});
$('.sync-profile').on('click',function(){const btn=$(this).prop('disabled',true);$.post(`${ROUTES.base}/${btn.data('id')}/sync`,{_token:@json(csrf_token())}).done(r=>{toastr.success(r.message);location.reload()}).fail(xhr=>toastr.error(xhr.responseJSON?.message||'Sync gagal')).always(()=>btn.prop('disabled',false))});
$('#syncAllProfiles').on('click',function(){const btn=$(this).prop('disabled',true);$.post(ROUTES.syncAll,{_token:@json(csrf_token())}).done(r=>{toastr.success(r.message);location.reload()}).fail(xhr=>toastr.error(xhr.responseJSON?.message||'Sync gagal')).always(()=>btn.prop('disabled',false))});
$('.delete-profile').on('click',function(){const id=$(this).data('id');Swal.fire({title:'Hapus profile?',text:'Profile akan dihapus dari SIMANSA dan group FreeRADIUS.',icon:'warning',showCancelButton:true,confirmButtonColor:'#dc3545',confirmButtonText:'Hapus'}).then(result=>{if(!result.isConfirmed)return;$.ajax({url:`${ROUTES.base}/${id}`,method:'DELETE',data:{_token:@json(csrf_token())}}).done(r=>{toastr.success(r.message);location.reload()}).fail(xhr=>toastr.error(xhr.responseJSON?.message||'Profile tidak dapat dihapus'))})});
</script>
@endsection
