@extends('adminlte::page')

@section('title', 'Catatan Siswa — Kelas Saya')

@section('content_header')
    <div class="simansa-hero">
        <div class="simansa-hero__main">
            <div class="simansa-hero__eyebrow"><i class="fas fa-sticky-note"></i> Kelas Saya</div>
            <h1 class="simansa-hero__title">Catatan Siswa</h1>
            <p class="simansa-hero__subtitle">Catatan pembinaan {{ $kelas->nama_kelas }}. Dapat dibaca guru BK/konseling.</p>
        </div>
    </div>
@stop

@section('content')
    @includeWhen($kelasList->count() > 1, 'admin.gtk.wali.partials.kelas-switcher', ['route' => 'admin.gtk.wali.catatan.index'])

    @if(session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    @endif

    <div class="row">
        <div class="col-lg-4">
            <div class="card simansa-management-card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-plus"></i> Tambah Catatan</h3></div>
                <form method="POST" action="{{ route('admin.gtk.wali.catatan.store') }}">
                    @csrf
                    <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                    <div class="card-body">
                        <div class="form-group">
                            <label>Siswa <span class="text-danger">*</span></label>
                            <select name="siswa_id" class="form-control" required>
                                <option value="">-- Pilih Siswa --</option>
                                @foreach($students as $s)
                                    <option value="{{ $s->id }}" {{ $filterSiswa === $s->id ? 'selected' : '' }}>{{ $s->nama_lengkap }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Kategori</label>
                            <select name="kategori" class="form-control">
                                <option value="">-- Umum --</option>
                                @foreach($kategoriList as $k => $label)
                                    <option value="{{ $k }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Catatan <span class="text-danger">*</span></label>
                            <textarea name="catatan" rows="3" class="form-control" maxlength="2000" required></textarea>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_penting" name="is_penting" value="1">
                                <label class="custom-control-label" for="is_penting">Tandai penting</label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-save"></i> Simpan Catatan</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card simansa-management-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list"></i> Daftar Catatan</h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.gtk.wali.catatan.index') }}" class="form-inline mb-3">
                        <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                        <select name="siswa_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                            <option value="">Semua siswa</option>
                            @foreach($students as $s)
                                <option value="{{ $s->id }}" {{ $filterSiswa === $s->id ? 'selected' : '' }}>{{ $s->nama_lengkap }}</option>
                            @endforeach
                        </select>
                        <select name="kategori" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">Semua kategori</option>
                            @foreach($kategoriList as $k => $label)
                                <option value="{{ $k }}" {{ $filterKategori === $k ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </form>

                    @forelse($catatan as $c)
                        <div class="border rounded p-3 mb-2 {{ $c->is_penting ? 'border-warning' : '' }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="font-weight-600">{{ optional($c->siswa)->nama_lengkap }}</span>
                                    @if($c->kategori)<span class="badge badge-info ml-1">{{ $c->kategori_label }}</span>@endif
                                    @if($c->is_penting)<span class="badge badge-warning ml-1"><i class="fas fa-star"></i> Penting</span>@endif
                                    @if($c->dibaca_bk_at)<span class="badge badge-success ml-1"><i class="fas fa-check"></i> Dibaca BK</span>@endif
                                    <div class="text-muted small">{{ $c->tanggal->translatedFormat('d M Y') }}</div>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary btn-edit-catatan"
                                        data-id="{{ $c->id }}"
                                        data-tanggal="{{ $c->tanggal->format('Y-m-d') }}"
                                        data-kategori="{{ $c->kategori }}"
                                        data-penting="{{ $c->is_penting ? 1 : 0 }}"
                                        data-catatan="{{ e($c->catatan) }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.gtk.wali.catatan.destroy', $c->id) }}" onsubmit="return confirm('Hapus catatan ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                            <div class="mt-2">{{ $c->catatan }}</div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Belum ada catatan.</p>
                    @endforelse

                    <div class="mt-3">{{ $catatan->links() }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal edit --}}
    <div class="modal fade" id="modalEditCatatan" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" id="formEditCatatan" class="modal-content">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Catatan</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" id="edit_tanggal" max="{{ date('Y-m-d') }}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="kategori" id="edit_kategori" class="form-control">
                            <option value="">-- Umum --</option>
                            @foreach($kategoriList as $k => $label)
                                <option value="{{ $k }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Catatan <span class="text-danger">*</span></label>
                        <textarea name="catatan" id="edit_catatan" rows="3" class="form-control" maxlength="2000" required></textarea>
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="edit_penting" name="is_penting" value="1">
                        <label class="custom-control-label" for="edit_penting">Tandai penting</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
    $(function () {
        var baseUrl = "{{ url('admin/gtk/wali/catatan') }}";
        $('.btn-edit-catatan').on('click', function () {
            var d = $(this).data();
            $('#formEditCatatan').attr('action', baseUrl + '/' + d.id);
            $('#edit_tanggal').val(d.tanggal);
            $('#edit_kategori').val(d.kategori || '');
            $('#edit_catatan').val(d.catatan);
            $('#edit_penting').prop('checked', d.penting == 1);
            $('#modalEditCatatan').modal('show');
        });
    });
</script>
@stop
