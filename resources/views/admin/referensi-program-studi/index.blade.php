@extends('adminlte::page')

@section('title', 'Referensi Program Studi')

@section('content_header')
    <h1>Referensi Program Studi</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Tambah Referensi Prodi</h3>
                </div>
                <form action="{{ route('admin.referensi-program-studi.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="referensi_perguruan_tinggi_id">Kampus</label>
                            <select name="referensi_perguruan_tinggi_id" id="referensi_perguruan_tinggi_id" class="form-control" required>
                                <option value="">Pilih Kampus</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" {{ old('referensi_perguruan_tinggi_id', $selectedCampusId) === $campus->id ? 'selected' : '' }}>
                                        {{ $campus->nama }} ({{ $campus->jenis }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="nama">Nama Prodi</label>
                            <input type="text" name="nama" id="nama" class="form-control" value="{{ old('nama') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="jenjang">Jenjang</label>
                            <input type="text" name="jenjang" id="jenjang" class="form-control" value="{{ old('jenjang') }}" placeholder="Contoh: S1, D4, PROFESI">
                        </div>
                        <div class="form-group">
                            <label for="fakultas">Bidang / Fakultas</label>
                            <input type="text" name="fakultas" id="fakultas" class="form-control" value="{{ old('fakultas') }}">
                        </div>
                        <div class="form-group">
                            <label for="sumber_referensi">Sumber Referensi</label>
                            <input type="text" name="sumber_referensi" id="sumber_referensi" class="form-control" value="{{ old('sumber_referensi', 'kurasi manual') }}">
                        </div>
                        <div class="form-group mb-0">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="is_active" class="custom-control-input" id="is_active" value="1" checked>
                                <label class="custom-control-label" for="is_active">Aktif</label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Sinkron PDDIKTI</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-2">Sinkronisasi resmi dijalankan dari server/terminal agar master prodi lokal mengikuti data PDDIKTI.</p>
                    <code>php artisan referensi:sync-prodi-pddikti --only-active</code>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Daftar Referensi Prodi</h3>
                    <div class="card-tools">
                        <form method="GET" action="{{ route('admin.referensi-program-studi.index') }}" id="studyProgramFilterForm">
                            <div class="input-group input-group-sm" style="width: 320px;">
                                <select name="referensi_perguruan_tinggi_id" class="form-control" id="studyProgramCampusFilter">
                                    <option value="">Semua Kampus</option>
                                    @foreach($campuses as $campus)
                                        <option value="{{ $campus->id }}" {{ $selectedCampusId === $campus->id ? 'selected' : '' }}>
                                            {{ $campus->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="submit">Filter</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div id="studyProgramListWrapper" class="position-relative">
                    <div id="studyProgramList">
                        @include('admin.referensi-program-studi.partials.table')
                    </div>
                    <div id="studyProgramLoading" class="study-program-loading d-none">
                        <div class="text-center text-muted">
                            <i class="fas fa-spinner fa-spin mr-1"></i> Memuat data...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .study-program-loading {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(1px);
            z-index: 10;
        }

        #studyProgramList.is-loading {
            opacity: 0.45;
            transition: opacity 0.2s ease;
        }
    </style>
@stop

@section('js')
    <script>
        $(function () {
            const $filterForm = $('#studyProgramFilterForm');
            const $campusFilter = $('#studyProgramCampusFilter');
            const $list = $('#studyProgramList');
            const $loading = $('#studyProgramLoading');

            let currentRequest = null;

            window.history.replaceState({ url: window.location.href }, '', window.location.href);

            function toggleLoading(isLoading) {
                $loading.toggleClass('d-none', !isLoading);
                $list.toggleClass('is-loading', isLoading);
            }

            function loadStudyPrograms(url, pushState = true) {
                if (currentRequest) {
                    currentRequest.abort();
                }

                toggleLoading(true);

                currentRequest = $.ajax({
                    url: url,
                    method: 'GET',
                    dataType: 'json',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                currentRequest.done(function (response) {
                    if (!response.html) {
                        window.location.href = url;
                        return;
                    }

                    $('.modal.show').modal('hide');
                    $('body').removeClass('modal-open');
                    $('.modal-backdrop').remove();

                    $list.html(response.html);

                    if (pushState) {
                        window.history.pushState({ url: url }, '', url);
                    }
                });

                currentRequest.fail(function (xhr, status) {
                    if (status !== 'abort') {
                        window.location.href = url;
                    }
                });

                currentRequest.always(function () {
                    toggleLoading(false);
                    currentRequest = null;
                });
            }

            $filterForm.on('submit', function (event) {
                event.preventDefault();

                const url = new URL($filterForm.attr('action'), window.location.origin);
                const campusId = $campusFilter.val();

                if (campusId) {
                    url.searchParams.set('referensi_perguruan_tinggi_id', campusId);
                }

                loadStudyPrograms(url.toString());
            });

            $campusFilter.on('change', function () {
                $filterForm.trigger('submit');
            });

            $(document).on('click', '#studyProgramList .pagination a', function (event) {
                event.preventDefault();

                const url = $(this).attr('href');
                if (url) {
                    loadStudyPrograms(url);
                }
            });

            window.addEventListener('popstate', function (event) {
                const url = event.state?.url || window.location.href;
                const parsedUrl = new URL(url);
                $campusFilter.val(parsedUrl.searchParams.get('referensi_perguruan_tinggi_id') || '');
                loadStudyPrograms(url, false);
            });
        });
    </script>
@stop
