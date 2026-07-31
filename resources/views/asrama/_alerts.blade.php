@foreach (['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $key => $type)
    @if(session($key))
        <div class="alert alert-{{ $type }} alert-dismissible fade show asrama-alert" role="alert">
            <i class="fas fa-{{ $type === 'success' ? 'check-circle' : ($type === 'warning' ? 'exclamation-triangle' : 'times-circle') }} mr-1"></i>
            {{ session($key) }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
@endforeach
@if($errors->any())
    <div class="alert alert-danger asrama-alert">
        <strong>Data belum dapat disimpan.</strong>
        <ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif
