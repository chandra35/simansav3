@extends('adminlte::page')
@section('title', 'Alumni Santri Asrama')

@section('content')
@include('asrama._alerts')
@php
    $heroTitle = 'Alumni Santri Asrama';
    $heroDescription = 'Database penelusuran lulusan santri asrama, riwayat kamar santri, serta integrasi pelacakan kelanjutan studi dan karier.';
    $heroAction = '<a href="'.route('admin.alumni.index', ['is_santri_asrama' => 1]).'" class="btn btn-light"><i class="fas fa-graduation-cap mr-1 text-primary"></i> Data Alumni Reguler</a>';
@endphp
@include('asrama._hero')

<div class="row">
    <div class="col-md-4 col-xl-2 mb-3">
        <div class="card card-outline card-primary h-100 shadow-sm"><div class="card-body py-3">
            <div class="text-muted small text-uppercase font-weight-bold">Total Alumni</div>
            <h3 class="text-primary mb-0 font-weight-bold">{{ number_format($stats['total']) }}</h3>
        </div></div>
    </div>
    <div class="col-md-4 col-xl-2 mb-3">
        <div class="card card-outline card-info h-100 shadow-sm"><div class="card-body py-3">
            <div class="text-muted small text-uppercase font-weight-bold">Santri (L)</div>
            <h3 class="text-info mb-0 font-weight-bold">{{ number_format($stats['laki']) }}</h3>
        </div></div>
    </div>
    <div class="col-md-4 col-xl-2 mb-3">
        <div class="card card-outline card-danger h-100 shadow-sm"><div class="card-body py-3">
            <div class="text-muted small text-uppercase font-weight-bold">Santriwati (P)</div>
            <h3 class="text-danger mb-0 font-weight-bold">{{ number_format($stats['perempuan']) }}</h3>
        </div></div>
    </div>
    <div class="col-md-4 col-xl-2 mb-3">
        <div class="card card-outline card-success h-100 shadow-sm"><div class="card-body py-3">
            <div class="text-muted small text-uppercase font-weight-bold">Kuliah</div>
            <h3 class="text-success mb-0 font-weight-bold">{{ number_format($stats['kuliah']) }}</h3>
        </div></div>
    </div>
    <div class="col-md-4 col-xl-2 mb-3">
        <div class="card card-outline card-warning h-100 shadow-sm"><div class="card-body py-3">
            <div class="text-muted small text-uppercase font-weight-bold">Pesantren</div>
            <h3 class="text-warning mb-0 font-weight-bold">{{ number_format($stats['pesantren']) }}</h3>
        </div></div>
    </div>
    <div class="col-md-4 col-xl-2 mb-3">
        <div class="card card-outline card-secondary h-100 shadow-sm"><div class="card-body py-3">
            <div class="text-muted small text-uppercase font-weight-bold">Bekerja</div>
            <h3 class="text-secondary mb-0 font-weight-bold">{{ number_format($stats['kerja']) }}</h3>
        </div></div>
    </div>
</div>

<div class="card card-outline card-primary shadow-sm">
    <div class="card-header bg-white"><h3 class="card-title font-weight-bold text-dark"><i class="fas fa-user-graduate mr-2 text-primary"></i>Daftar Alumni Santri Asrama</h3></div>
    <div class="card-body">
        <form method="get" id="filterForm">
            <div class="row">
                <div class="col-md-6 col-xl-4 mb-3">
                    <label class="small font-weight-bold text-muted" for="filterQ"><i class="fas fa-search mr-1"></i> Pencarian</label>
                    <input type="text" id="filterQ" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Nama / NISN / No. Induk Asrama">
                </div>
                <div class="col-md-6 col-xl-2 mb-3">
                    <label class="small font-weight-bold text-muted" for="filterJk"><i class="fas fa-venus-mars mr-1"></i> Jenis Kelamin</label>
                    <select id="filterJk" name="jenis_kelamin" class="form-control form-control-sm">
                        <option value="">Semua</option>
                        <option value="L" @selected(request('jenis_kelamin')==='L')>Laki-Laki (L)</option>
                        <option value="P" @selected(request('jenis_kelamin')==='P')>Perempuan (P)</option>
                    </select>
                </div>
                <div class="col-md-6 col-xl-3 mb-3">
                    <label class="small font-weight-bold text-muted" for="filterTahun"><i class="fas fa-calendar-alt mr-1"></i> Tahun Keluar / Lulus</label>
                    <select id="filterTahun" name="tahun_lulus" class="form-control form-control-sm">
                        <option value="">Semua Tahun</option>
                        @foreach($tahunLulusList as $thn)
                            <option value="{{ $thn }}" @selected(request('tahun_lulus') == $thn)>{{ $thn }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-xl-3 mb-3">
                    <label class="small font-weight-bold text-muted" for="filterStatusLanjut"><i class="fas fa-briefcase mr-1"></i> Aktivitas Pasca Lulus</label>
                    <select id="filterStatusLanjut" name="status_lanjut" class="form-control form-control-sm">
                        <option value="">Semua Status</option>
                        <option value="kuliah" @selected(request('status_lanjut') === 'kuliah')>Melanjutkan Studi / Kuliah</option>
                        <option value="pesantren" @selected(request('status_lanjut') === 'pesantren')>Pesantren</option>
                        <option value="bekerja" @selected(request('status_lanjut') === 'bekerja')>Bekerja</option>
                        <option value="wirausaha" @selected(request('status_lanjut') === 'wirausaha')>Wirausaha</option>
                        <option value="belum_terdata" @selected(request('status_lanjut') === 'belum_terdata')>Belum Terdata</option>
                        <option value="lainnya" @selected(request('status_lanjut') === 'lainnya')>Lainnya</option>
                    </select>
                </div>
            </div>
            <div class="d-flex justify-content-end mb-3">
                <button type="submit" class="btn btn-sm btn-primary mr-2"><i class="fas fa-filter mr-1"></i> Terapkan Filter</button>
                <a href="{{ route('asrama.alumni.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-redo mr-1"></i> Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped table-sm align-middle">
                <thead class="thead-light">
                    <tr>
                        <th class="text-center" style="width: 50px">Foto</th>
                        <th>Santri & NIA</th>
                        <th class="text-center">JK</th>
                        <th>Tahun Lulus</th>
                        <th>Riwayat Kamar</th>
                        <th>Aktivitas Sekarang</th>
                        <th>Kontak</th>
                        <th class="text-center" style="width: 100px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $item)
                        @php
                            $siswa = $item->siswa;
                            $profile = $siswa?->alumniProfile;
                            $fallback = 'https://ui-avatars.com/api/?name='.urlencode($siswa->nama_lengkap ?? 'Santri').'&size=100&background='.($siswa?->jenis_kelamin === 'L' ? '3498db' : 'e83e8c').'&color=FFFFFF&font-size=0.45&bold=true';
                            $fotoUrl = $siswa?->foto_profile ? $siswa->foto_profile_url : $fallback;
                            $kamarTerakhir = $item->kamarRecords->sortByDesc('created_at')->first()?->kamar?->nama ?? '-';
                        @endphp
                        <tr>
                            <td class="text-center align-middle">
                                @if($siswa?->foto_profile)
                                    <button type="button" class="btn btn-link p-0 border-0 js-preview-foto" data-preview-url="{{ $fotoUrl }}" data-student-name="{{ $siswa->nama_lengkap }}" title="Klik preview foto">
                                        <img src="{{ $fotoUrl }}" alt="Foto {{ $siswa->nama_lengkap }}" class="img-circle shadow-sm" onerror="this.onerror=null;this.src='{{ $fallback }}';" style="width:36px;height:36px;object-fit:cover;">
                                    </button>
                                @else
                                    <img src="{{ $fallback }}" alt="{{ $siswa?->nama_lengkap }}" class="img-circle" style="width:36px;height:36px;object-fit:cover;opacity:.7;">
                                @endif
                            </td>
                            <td class="align-middle">
                                <strong>{{ $siswa?->nama_lengkap ?: '-' }}</strong><br>
                                <small class="text-muted">NISN: {{ $siswa?->nisn ?: '-' }} &middot; NIA: <code class="font-weight-bold text-success">{{ $item->nomor_induk_asrama ?: '-' }}</code></small>
                            </td>
                            <td class="text-center align-middle">
                                {!! ($siswa?->jenis_kelamin === 'L') ? '<span class="badge badge-primary">L</span>' : '<span class="badge badge-danger">P</span>' !!}
                            </td>
                            <td class="align-middle">
                                <span class="badge badge-light border font-weight-bold">
                                    {{ $profile?->tahun_lulus ?: ($item->tanggal_keluar?->format('Y') ?: '-') }}
                                </span>
                                @if($item->tanggal_masuk)
                                    <br><small class="text-muted">Mondok: {{ $item->tanggal_masuk->format('Y') }} - {{ $item->tanggal_keluar?->format('Y') ?: 'Lulus' }}</small>
                                @endif
                            </td>
                            <td class="align-middle">
                                <i class="fas fa-bed text-muted mr-1"></i>{{ $kamarTerakhir }}
                            </td>
                            <td class="align-middle">
                                @if($profile)
                                    @php
                                        $badgeColor = match($profile->status_setelah_lulus) {
                                            'kuliah' => 'badge-success',
                                            'pesantren' => 'badge-warning',
                                            'bekerja' => 'badge-info',
                                            'wirausaha' => 'badge-primary',
                                            default => 'badge-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeColor }} mb-1">{{ $profile->status_label }}</span>
                                    @if($profile->institusi_lanjutan || $profile->instansi)
                                        <div class="small font-weight-bold">{{ $profile->institusi_lanjutan ?: $profile->instansi }}</div>
                                    @endif
                                    @if($profile->program_studi || $profile->pekerjaan)
                                        <div class="small text-muted">{{ $profile->program_studi ?: $profile->pekerjaan }}</div>
                                    @endif
                                @else
                                    <span class="badge badge-secondary">Belum terdata</span>
                                @endif
                            </td>
                            <td class="align-middle small">
                                @if($profile?->nomor_hp)
                                    <div><i class="fab fa-whatsapp text-success mr-1"></i>{{ $profile->nomor_hp }}</div>
                                @endif
                                @if($profile?->email)
                                    <div class="text-muted"><i class="fas fa-envelope mr-1"></i>{{ $profile->email }}</div>
                                @endif
                                @if(!$profile?->nomor_hp && !$profile?->email)
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center align-middle text-nowrap">
                                <button type="button" class="btn btn-sm btn-info" onclick="showSantri('{{ $item->id }}')" title="Detail santri"><i class="fas fa-eye"></i></button>
                                @if($profile)
                                    <a href="{{ route('admin.alumni.show', $profile) }}" class="btn btn-sm btn-outline-primary" title="Profil Alumni Lengkap"><i class="fas fa-external-link-alt"></i></a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-user-graduate d-block mb-2 text-muted" style="font-size: 2.5rem; opacity: .4;"></i>
                                Tidak ada data alumni santri yang cocok dengan kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mt-3">
            <small class="text-muted mb-2 mb-md-0">Menampilkan {{ $records->firstItem() ?: 0 }}&ndash;{{ $records->lastItem() ?: 0 }} dari {{ number_format($records->total()) }} alumni santri</small>
            {{ $records->links() }}
        </div>
    </div>
</div>

{{-- Modal Preview Foto --}}
<div class="modal fade" id="fotoPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-camera-retro mr-2"></i>Preview Foto Santri</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center">
                <p class="font-weight-bold mb-3" id="fotoPreviewName">-</p>
                <img id="fotoPreviewImage" src="" alt="Preview foto" class="img-fluid rounded shadow-sm border" style="max-height:65vh;object-fit:contain;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Detail Santri --}}
<div class="modal fade" id="viewSantriModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-user mr-2"></i>Detail Alumni Santri Asrama</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="santriDetailBody">
                <div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><div class="mt-2 text-muted">Memuat data santri...</div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    $(document).on('click', '.js-preview-foto', function() {
        var url = $(this).data('preview-url');
        var name = $(this).data('student-name');
        $('#fotoPreviewName').text(name);
        $('#fotoPreviewImage').attr('src', url);
        $('#fotoPreviewModal').modal('show');
    });
});

function showSantri(id) {
    $('#santriDetailBody').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><div class="mt-2 text-muted">Memuat data santri...</div></div>');
    $('#viewSantriModal').modal('show');
    $.get('/asrama/santri/' + id + '/detail', function(response) {
        if (!response.success) {
            $('#santriDetailBody').html('<div class="alert alert-danger">Gagal memuat data santri.</div>');
            return;
        }
        var s = response.data;
        var sw = s.siswa || {};
        var html = '<div class="row">' +
            '<div class="col-md-4 text-center border-right">' +
                '<img src="' + (sw.foto_profile_url || "https://ui-avatars.com/api/?name=" + encodeURIComponent(sw.nama_lengkap || "Santri") + "&size=150") + '" class="img-circle img-thumbnail mb-3" style="width:140px;height:140px;object-fit:cover;">' +
                '<h5 class="font-weight-bold mb-1">' + (sw.nama_lengkap || '-') + '</h5>' +
                '<span class="badge badge-success mb-2">Alumni Santri</span>' +
                '<p class="text-muted small">Nomor Induk Asrama: <br><strong class="text-dark">' + (s.nomor_induk_asrama || '-') + '</strong></p>' +
            '</div>' +
            '<div class="col-md-8">' +
                '<div class="row">' +
                    '<div class="col-sm-6 mb-2"><strong>NISN:</strong> ' + (sw.nisn || '-') + '</div>' +
                    '<div class="col-sm-6 mb-2"><strong>Jenis Kelamin:</strong> ' + (sw.jenis_kelamin === 'L' ? 'Laki-Laki' : 'Perempuan') + '</div>' +
                    '<div class="col-sm-6 mb-2"><strong>Tanggal Masuk Asrama:</strong> ' + (s.tanggal_masuk || '-') + '</div>' +
                    '<div class="col-sm-6 mb-2"><strong>Tanggal Lulus/Keluar:</strong> ' + (s.tanggal_keluar || '-') + '</div>' +
                    '<div class="col-sm-12 mb-2"><strong>Sekolah Asal:</strong> ' + (sw.sekolah_asal ? sw.sekolah_asal.nama : '-') + '</div>' +
                    '<div class="col-sm-12 mb-2"><strong>Kamar Terakhir:</strong> ' + (s.kamar_aktif ? (s.kamar_aktif.kamar ? s.kamar_aktif.kamar.nama : '-') : 'Sudah keluar') + '</div>' +
                    '<div class="col-sm-12 mt-2"><strong>Catatan:</strong> <p class="text-muted small">' + (s.catatan || 'Tidak ada catatan') + '</p></div>' +
                '</div>' +
            '</div>' +
        '</div>';
        $('#santriDetailBody').html(html);
    }).fail(function() {
        $('#santriDetailBody').html('<div class="alert alert-danger">Terjadi kesalahan saat memuat data.</div>');
    });
}
</script>
@stop