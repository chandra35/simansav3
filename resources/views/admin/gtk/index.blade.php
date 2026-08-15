@extends('adminlte::page')

@section('title', 'Data GTK - SIMANSA')


@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-chalkboard-teacher text-primary"></i> Data GTK</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Data GTK</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="simansa-gtk-management">
<div class="card bg-gradient-primary text-white mb-4 simansa-gtk-hero">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h3 class="mb-1"><i class="fas fa-address-card mr-1"></i> Manajemen Guru & Tenaga Kependidikan</h3>
                <p class="mb-0 text-white-75">
                    Kelola identitas GTK, pantau kelengkapan data, dan jalankan sinkronisasi Kemenag dari satu halaman operasional.
                </p>
            </div>
            <div class="col-lg-4 mt-3 mt-lg-0">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="text-white-75 small text-uppercase font-weight-bold">Total GTK</div>
                        <h3 class="mb-0 text-white">{{ number_format($stats['total_gtk']) }}</h3>
                    </div>
                    <div class="col-6">
                        <div class="text-white-75 small text-uppercase font-weight-bold">Siap Sinkron</div>
                        <h3 class="mb-0 text-white">{{ number_format($stats['gtk_with_nip']) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card card-outline card-primary h-100 simansa-gtk-stat">
            <div class="card-body">
                <div class="text-muted small text-uppercase font-weight-bold">Total GTK</div>
                <h3 class="text-primary mb-1">{{ number_format($stats['total_gtk']) }}</h3>
                <div class="text-muted">Guru dan tenaga kependidikan berstatus aktif. {{ number_format($stats['nonaktif']) }} GTK nonaktif tetap tersimpan pada histori.</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card card-outline card-info h-100 simansa-gtk-stat">
            <div class="card-body">
                <div class="text-muted small text-uppercase font-weight-bold">Laki-Laki</div>
                <h3 class="text-info mb-1">{{ number_format($stats['laki_laki']) }}</h3>
                <div class="text-muted">Jumlah GTK laki-laki untuk monitoring personalia.</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card card-outline card-danger h-100 simansa-gtk-stat">
            <div class="card-body">
                <div class="text-muted small text-uppercase font-weight-bold">Perempuan</div>
                <h3 class="text-danger mb-1">{{ number_format($stats['perempuan']) }}</h3>
                <div class="text-muted">Jumlah GTK perempuan sesuai data aktif yang tersimpan.</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card card-outline card-success h-100 simansa-gtk-stat">
            <div class="card-body">
                <div class="text-muted small text-uppercase font-weight-bold">Data Lengkap</div>
                <h3 class="text-success mb-1">{{ number_format($stats['data_lengkap']) }}</h3>
                <div class="text-muted">GTK dengan data pribadi dan kepegawaian yang sudah lengkap.</div>
            </div>
        </div>
    </div>
</div>

<div class="card card-outline card-primary simansa-gtk-card">
    <div class="card-header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
        <h3 class="card-title mb-3 mb-lg-0">
            <i class="fas fa-list mr-2"></i>
            Daftar GTK
        </h3>
        <div class="card-tools ml-0 simansa-gtk-actions">
            @can('edit-gtk')
                <button type="button" class="btn btn-warning btn-sm mr-1" id="btnBulkSyncKemenag">
                    <i class="fas fa-sync-alt"></i> Sinkron Semua GTK Ber-NIP
                    <span class="badge badge-light ml-1">{{ $stats['gtk_with_nip'] }}</span>
                </button>
            @endcan
            @can('create-gtk')
                <a href="{{ route('admin.gtk.import') }}" class="btn btn-success btn-sm mr-1">
                    <i class="fas fa-file-excel"></i> Import GTK
                </a>
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addGtkModal">
                    <i class="fas fa-plus"></i> Tambah GTK
                </button>
            @endcan
        </div>
        </div>
    </div>
    <div class="card-body">
        {{-- Filter Section --}}
        <div class="simansa-filter-panel simansa-gtk-filter">
            <form id="filterForm">
                <div class="row">
                            <div class="col-md-6 col-xl-2 mb-3">
                                <label for="filterKategoriPtk" class="simansa-filter-label">
                                    <i class="fas fa-users mr-1"></i> Kategori PTK
                                </label>
                                <select id="filterKategoriPtk" class="form-control form-control-sm">
                                    <option value="">Semua</option>
                                    <option value="Pendidik">Pendidik (Guru)</option>
                                    <option value="Tenaga Kependidikan">Tenaga Kependidikan</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-xl-2 mb-3">
                                <label for="filterJenisPtk" class="simansa-filter-label">
                                    <i class="fas fa-user-tag mr-1"></i> Jenis PTK
                                </label>
                                <select id="filterJenisPtk" class="form-control form-control-sm">
                                    <option value="">Semua</option>
                                    <option value="Guru Mapel">Guru Mapel</option>
                                    <option value="Guru BK">Guru BK</option>
                                    <option value="Kepala TU">Kepala TU</option>
                                    <option value="Staff TU">Staff TU</option>
                                    <option value="Bendahara">Bendahara</option>
                                    <option value="Laboran">Laboran</option>
                                    <option value="Pustakawan">Pustakawan</option>
                                    <option value="Cleaning Service">Cleaning Service</option>
                                    <option value="Satpam">Satpam</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-xl-2 mb-3">
                                <label for="filterJenisKelamin" class="simansa-filter-label">
                                    <i class="fas fa-venus-mars mr-1"></i> Jenis Kelamin
                                </label>
                                <select id="filterJenisKelamin" class="form-control form-control-sm">
                                    <option value="">Semua</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-xl-2 mb-3">
                                <label for="filterStatusAktif" class="simansa-filter-label"><i class="fas fa-user-check mr-1"></i> Keaktifan</label>
                                <select id="filterStatusAktif" class="form-control form-control-sm"><option value="1">Aktif</option><option value="">Semua</option><option value="0">Nonaktif</option></select>
                            </div>
                            <div class="col-md-6 col-xl-2 mb-3">
                                <label for="filterStatusKepegawaian" class="simansa-filter-label">
                                    <i class="fas fa-briefcase mr-1"></i> Status Kepeg
                                </label>
                                <select id="filterStatusKepegawaian" class="form-control form-control-sm">
                                    <option value="">Semua</option>
                                    @foreach($statusKepegawaianOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-xl-2 mb-3">
                                <label for="filterStatus" class="simansa-filter-label">
                                    <i class="fas fa-database mr-1"></i> Status Data
                                </label>
                                <select id="filterStatus" class="form-control form-control-sm">
                                    <option value="">Semua</option>
                                    <option value="lengkap">Data Lengkap</option>
                                    <option value="belum">Belum Lengkap</option>
                                </select>
                            </div>
                </div>
                <div class="simansa-gtk-filter-footer">
                    <span id="gtkFilterStatus" class="simansa-gtk-filter-status" aria-live="polite">
                        <i class="fas fa-bolt mr-1"></i> Filter memuat data secara otomatis
                    </span>
                    <button type="button" id="btnResetFilter" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-redo"></i> Reset Filter
                    </button>
                </div>
            </form>
        </div>

        <p class="simansa-gtk-table-note">
            Gunakan filter untuk memantau komposisi GTK, kelengkapan data, dan kesiapan sinkronisasi Kemenag tanpa meninggalkan halaman ini.
        </p>

        <div class="simansa-gtk-table-wrap">
            <table id="gtk-table" class="table table-hover table-sm align-middle simansa-gtk-table">
                <thead>
                    <tr>
                        <th class="gtk-col-number">No</th>
                        <th class="gtk-col-profile">Profil</th>
                        <th class="gtk-col-role">Peran</th>
                        <th class="gtk-col-status">Status</th>
                        <th class="gtk-col-actions">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div id="bulkSyncOverlay" class="bulk-sync-overlay d-none">
    <div class="bulk-sync-panel">
        <div class="bulk-sync-header">
            <div>
                <div class="bulk-sync-eyebrow">Sinkronisasi GTK Kemenag</div>
                <h4 class="bulk-sync-title mb-0">Memproses data GTK ber-NIP</h4>
            </div>
            <div class="bulk-sync-spinner">
                <i class="fas fa-sync-alt fa-spin"></i>
            </div>
        </div>

        <div class="bulk-sync-meta">
            <div class="bulk-sync-stat">
                <span class="bulk-sync-stat-label">Progress</span>
                <span class="bulk-sync-stat-value" id="bulkSyncProgressText">0 / 0</span>
            </div>
            <div class="bulk-sync-stat">
                <span class="bulk-sync-stat-label">Berhasil</span>
                <span class="bulk-sync-stat-value text-success" id="bulkSyncSuccessCount">0</span>
            </div>
            <div class="bulk-sync-stat">
                <span class="bulk-sync-stat-label">Perubahan</span>
                <span class="bulk-sync-stat-value text-info" id="bulkSyncChangedCount">0</span>
            </div>
            <div class="bulk-sync-stat">
                <span class="bulk-sync-stat-label">Gagal</span>
                <span class="bulk-sync-stat-value text-danger" id="bulkSyncFailedCount">0</span>
            </div>
        </div>

        <div class="bulk-sync-progress-wrap">
            <div class="progress bulk-sync-progress">
                <div id="bulkSyncProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-warning" style="width: 0%">0%</div>
            </div>
            <div class="bulk-sync-note" id="bulkSyncCurrentLabel">Menyiapkan sinkronisasi...</div>
        </div>

        <div class="bulk-sync-log card">
            <div class="card-header py-2">
                <strong><i class="fas fa-stream mr-1"></i> Aktivitas Terakhir</strong>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush" id="bulkSyncLogList">
                    <li class="list-group-item text-muted">Belum ada proses yang berjalan.</li>
                </ul>
            </div>
        </div>

        <div class="bulk-sync-footer">
            <div class="text-muted small">
                Sinkronisasi massal ini hanya mengambil dan menyimpan hasil perbandingan dari Kemenag. Data lokal tidak diubah otomatis.
            </div>
        </div>
    </div>
</div>

{{-- Modal Add GTK --}}
<div class="modal fade" id="addGtkModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i> Tambah GTK Baru
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addGtkForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Informasi:</strong> Username akan dibuat otomatis dari NIK. Password default adalah NIK.
                    </div>
                    
                    <div class="form-group">
                        <label for="nama_lengkap">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                        <span class="invalid-feedback d-block" id="error-nama_lengkap"></span>
                    </div>

                    <div class="form-group">
                        <label for="nik">NIK (16 digit) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nik" name="nik" maxlength="16" required>
                        <small class="form-text text-muted">NIK akan digunakan sebagai username dan password default</small>
                        <span class="invalid-feedback d-block" id="error-nik"></span>
                    </div>

                    <div class="form-group">
                        <label for="jenis_kelamin">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select class="form-control" id="jenis_kelamin" name="jenis_kelamin" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                          <span class="invalid-feedback d-block" id="error-jenis_kelamin"></span>
                      </div>

                      <div class="form-group">
                          <label for="tanggal_efektif">Tanggal Mulai/TMT <span class="text-danger">*</span></label>
                          <input type="date" class="form-control" id="tanggal_efektif" name="tanggal_efektif" value="{{ today()->toDateString() }}" max="{{ today()->toDateString() }}" required>
                          <small class="form-text text-muted">Dicatat sebagai histori awal GTK baru.</small>
                          <span class="invalid-feedback d-block" id="error-tanggal_efektif"></span>
                      </div>

                    <div class="form-group">
                        <label for="kategori_ptk">Kategori PTK <span class="text-danger">*</span></label>
                        <select class="form-control" id="kategori_ptk" name="kategori_ptk" required>
                            <option value="">Pilih Kategori PTK</option>
                            <option value="Pendidik">Pendidik (Guru)</option>
                            <option value="Tenaga Kependidikan">Tenaga Kependidikan (Staff TU, dll)</option>
                        </select>
                        <small class="form-text text-muted">Kategori PTK: Pendidik untuk Guru, Tenaga Kependidikan untuk Staff non-Guru</small>
                        <span class="invalid-feedback d-block" id="error-kategori_ptk"></span>
                    </div>

                    <div class="form-group">
                        <label for="jenis_ptk">Jenis PTK <span class="text-danger">*</span></label>
                        <select class="form-control" id="jenis_ptk" name="jenis_ptk" required disabled>
                            <option value="">Pilih Kategori PTK terlebih dahulu</option>
                        </select>
                        <small class="form-text text-muted">Jenis PTK akan muncul setelah memilih Kategori PTK</small>
                        <span class="invalid-feedback d-block" id="error-jenis_ptk"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal View GTK --}}
<div class="modal fade" id="viewGtkModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title">
                    <i class="fas fa-eye"></i> Detail GTK
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewGtkContent">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-3x"></i>
                    <p>Loading...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Update Foto Profil GTK --}}
<div class="modal fade" id="gtkPhotoModal" tabindex="-1" role="dialog" aria-labelledby="gtkPhotoModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content gtk-photo-modal">
            <div class="modal-header gtk-photo-modal__header">
                <div>
                    <small class="gtk-photo-modal__eyebrow"><i class="fas fa-camera mr-1"></i> FOTO PROFIL GTK</small>
                    <h5 class="modal-title" id="gtkPhotoModalTitle">Update Foto Profil</h5>
                    <span id="gtkPhotoGtkName">-</span>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="gtk-photo-layout">
                    <aside class="gtk-photo-current">
                        <span>Foto saat ini</span>
                        <div class="gtk-photo-current__frame">
                            <img id="gtkPhotoCurrent" alt="Foto profil GTK saat ini">
                            <i id="gtkPhotoCurrentEmpty" class="fas fa-user"></i>
                        </div>
                        <small>Hasil akhir disimpan dalam rasio potret 4:5 dengan ukuran 720 × 900 piksel.</small>
                    </aside>
                    <div class="gtk-photo-workspace">
                        <input type="file" id="gtkPhotoInput" accept="image/jpeg,image/png,image/webp" hidden>
                        <div class="gtk-photo-dropzone" id="gtkPhotoDropzone" role="button" tabindex="0" aria-label="Pilih atau seret foto GTK">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <strong>Seret foto ke sini</strong>
                            <span>atau klik untuk memilih file</span>
                            <small>JPG, PNG, WEBP · file asli maksimal 20 MB · otomatis dikompresi</small>
                        </div>
                        <div class="gtk-photo-cropper d-none" id="gtkPhotoCropperWrap">
                            <div class="gtk-photo-cropper__canvas"><img id="gtkPhotoCropImage" alt="Area crop foto GTK"></div>
                            <div class="gtk-photo-controls" aria-label="Kontrol crop foto">
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-photo-control="zoom-out" title="Perkecil"><i class="fas fa-search-minus"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-photo-control="zoom-in" title="Perbesar"><i class="fas fa-search-plus"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-photo-control="rotate-left" title="Putar kiri"><i class="fas fa-undo"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-photo-control="rotate-right" title="Putar kanan"><i class="fas fa-redo"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-primary ml-auto" id="gtkPhotoChooseAgain"><i class="fas fa-image mr-1"></i>Pilih Ulang</button>
                            </div>
                            <div class="gtk-photo-file-meta" id="gtkPhotoFileMeta"></div>
                        </div>
                        <div class="gtk-photo-upload-progress d-none" id="gtkPhotoProgress">
                            <div class="d-flex justify-content-between mb-1"><strong id="gtkPhotoProgressLabel">Menyiapkan foto…</strong><span id="gtkPhotoProgressValue">0%</span></div>
                            <div class="progress"><div class="progress-bar progress-bar-striped progress-bar-animated" id="gtkPhotoProgressBar" style="width:0%"></div></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <small class="text-muted mr-auto"><i class="fas fa-shield-alt text-success mr-1"></i>Foto lama baru dihapus setelah foto baru berhasil tersimpan.</small>
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="gtkPhotoSave" disabled><i class="fas fa-save mr-1"></i>Crop, Kompres & Simpan</button>
            </div>
        </div>
    </div>
</div>

</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
<style>
.simansa-gtk-management .simansa-gtk-avatar-trigger{padding:0;border:0;cursor:pointer}.simansa-gtk-management .simansa-gtk-avatar-trigger:focus-visible{outline:3px solid rgba(37,99,235,.35);outline-offset:3px}.gtk-detail-profile{display:grid;place-items:center;gap:.65rem;height:100%;padding:1rem;border:1px solid #dbeafe;border-radius:14px;background:linear-gradient(145deg,#f8fbff,#eff6ff);text-align:center}.gtk-detail-profile__photo{width:min(100%,180px);aspect-ratio:4/5;overflow:hidden;border:3px solid #fff;border-radius:16px;background:#dbeafe;box-shadow:0 10px 24px rgba(37,99,235,.16)}.gtk-detail-profile__photo img{width:100%;height:100%;object-fit:cover}.gtk-detail-profile__name{font-weight:800;color:#0f172a}.gtk-detail-profile__meta{color:#64748b;font-size:.78rem}
.gtk-photo-modal{overflow:hidden;border:0;border-radius:18px;box-shadow:0 24px 65px rgba(15,23,42,.3)}
.gtk-photo-modal__header{align-items:flex-start;border:0;background:linear-gradient(135deg,#2563eb,#0f766e);color:#fff;padding:1rem 1.25rem}.gtk-photo-modal__eyebrow{display:block;font-size:.65rem;font-weight:900;letter-spacing:.1em;opacity:.85}.gtk-photo-modal__header h5{margin:.15rem 0 0;font-weight:900}.gtk-photo-modal__header span{font-size:.78rem;opacity:.8}
.gtk-photo-layout{display:grid;grid-template-columns:170px minmax(0,1fr);gap:1rem}.gtk-photo-current{padding:.85rem;border:1px solid #dbeafe;border-radius:14px;background:#f8fbff;text-align:center}.gtk-photo-current>span{display:block;margin-bottom:.6rem;color:#475569;font-size:.7rem;font-weight:900;text-transform:uppercase}.gtk-photo-current__frame{display:grid;place-items:center;width:120px;aspect-ratio:4/5;margin:auto;overflow:hidden;border:3px solid #fff;border-radius:16px;background:linear-gradient(145deg,#dbeafe,#e0f2fe);box-shadow:0 8px 20px rgba(37,99,235,.14)}.gtk-photo-current__frame img{width:100%;height:100%;object-fit:cover}.gtk-photo-current__frame i{color:#60a5fa;font-size:2.4rem}.gtk-photo-current>small{display:block;margin-top:.7rem;color:#64748b;font-size:.68rem;line-height:1.45}
.gtk-photo-workspace{min-width:0}.gtk-photo-dropzone{display:flex;min-height:300px;align-items:center;justify-content:center;flex-direction:column;padding:1.2rem;border:2px dashed #93c5fd;border-radius:15px;background:linear-gradient(145deg,#f8fbff,#eff6ff);color:#475569;text-align:center;cursor:pointer;transition:.18s ease}.gtk-photo-dropzone:hover,.gtk-photo-dropzone:focus,.gtk-photo-dropzone.is-dragging{border-color:#2563eb;background:#dbeafe;outline:0;transform:translateY(-1px)}.gtk-photo-dropzone>i{margin-bottom:.7rem;color:#2563eb;font-size:2.7rem}.gtk-photo-dropzone strong,.gtk-photo-dropzone span,.gtk-photo-dropzone small{display:block}.gtk-photo-dropzone strong{color:#0f172a;font-size:1rem}.gtk-photo-dropzone span{font-size:.8rem}.gtk-photo-dropzone small{margin-top:.55rem;color:#64748b;font-size:.7rem}
.gtk-photo-cropper__canvas{height:min(52vh,430px);overflow:hidden;border-radius:14px;background:#0f172a}.gtk-photo-cropper__canvas img{display:block;max-width:100%}.gtk-photo-controls{display:flex;align-items:center;gap:.4rem;margin-top:.65rem}.gtk-photo-file-meta{margin-top:.55rem;color:#64748b;font-size:.72rem}.gtk-photo-upload-progress{padding:.85rem;border-radius:12px;background:#eff6ff;color:#1e3a8a;font-size:.75rem}.gtk-photo-upload-progress .progress{height:9px;border-radius:999px}.gtk-photo-upload-progress .progress-bar{background:linear-gradient(90deg,#2563eb,#14b8a6)}
@media(max-width:767.98px){.gtk-photo-layout{grid-template-columns:1fr}.gtk-photo-current{display:grid;grid-template-columns:85px 1fr;align-items:center;text-align:left}.gtk-photo-current>span{grid-column:1/-1}.gtk-photo-current__frame{width:72px;margin:0}.gtk-photo-current>small{margin:0}.gtk-photo-cropper__canvas{height:42vh}.gtk-photo-modal .modal-footer{align-items:stretch;flex-direction:column}.gtk-photo-modal .modal-footer small{margin-bottom:.35rem}.gtk-photo-modal .modal-footer .btn{width:100%}}
</style>
@stop


@section('js')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>

<script>
const BULK_SYNC_DELAY_MS = 350;
let gtkPhotoCropper = null;
let gtkPhotoContext = null;

$(document).ready(function() {
    const $gtkTableElement = $('#gtk-table');
    const $gtkTableWrap = $('.simansa-gtk-table-wrap');
    const $gtkFilterStatus = $('#gtkFilterStatus');
    let filterReloadTimer = null;

    const refreshGtkTooltips = function() {
        $gtkTableElement.find('[data-tooltip="true"]').tooltip({
            container: 'body',
            boundary: 'window',
            trigger: 'hover focus'
        });
    };

    const positionGtkActionMenus = function() {
        const $rows = $gtkTableElement.find('tbody tr');
        $rows.find('.simansa-gtk-action-menu').removeClass('dropup');
        $rows.slice(-3).find('.simansa-gtk-action-menu').addClass('dropup');
    };

    $gtkTableElement.on('click', '[data-gtk-photo-detail]', function() {
        showGtk(this.dataset.gtkPhotoDetail);
    });

    // Tombol dibuat oleh DataTables setelah halaman dimuat, sehingga aksi
    // harus ditangani lewat delegasi event, bukan inline onclick.
    $gtkTableElement.on('click', '.simansa-gtk-action-item', function(event) {
        event.preventDefault();
        handleGtkAction(this);
    });

    $gtkTableElement
        .on('preXhr.dt', function() {
            $gtkTableWrap.addClass('is-loading');
            $gtkFilterStatus
                .addClass('is-loading')
                .html('<i class="fas fa-circle-notch fa-spin mr-1"></i> Memuat data GTK...');
        })
        .on('xhr.dt', function() {
            $gtkTableWrap.removeClass('is-loading');
            $gtkFilterStatus
                .removeClass('is-loading')
                .html('<i class="fas fa-check-circle mr-1"></i> Data GTK sudah diperbarui');
        })
        .on('error.dt', function() {
            $gtkTableWrap.removeClass('is-loading');
            $gtkFilterStatus
                .removeClass('is-loading')
                .html('<i class="fas fa-exclamation-circle mr-1"></i> Data gagal dimuat');
        })
        .on('preDraw.dt', function() {
            $gtkTableElement.find('[data-tooltip="true"]').tooltip('dispose');
        })
        .on('draw.dt', function() {
            refreshGtkTooltips();
            positionGtkActionMenus();
        })
        .on('show.bs.dropdown', '.simansa-gtk-action-menu', function() {
            $(this).find('[data-tooltip="true"]').tooltip('hide');
            $gtkTableWrap.addClass('simansa-action-dropdown-open');
        })
        .on('hidden.bs.dropdown', '.simansa-gtk-action-menu', function() {
            $gtkTableWrap.removeClass('simansa-action-dropdown-open');
        });

    let gtkTable = $('#gtk-table').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        searchDelay: 350,
        ajax: {
            url: '{{ route('admin.gtk.data') }}',
            data: function(d) {
                d.kategori_ptk = $('#filterKategoriPtk').val();
                d.jenis_ptk = $('#filterJenisPtk').val();
                d.jenis_kelamin = $('#filterJenisKelamin').val();
                d.status_kepegawaian = $('#filterStatusKepegawaian').val();
                d.status = $('#filterStatus').val();
                d.status_aktif = $('#filterStatusAktif').val();
            }
        },
        autoWidth: true,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        pageLength: 10,
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'gtk-col-number align-middle' },
            { data: 'identity', name: 'nama_lengkap', className: 'gtk-col-profile align-middle' },
            { data: 'role_summary', name: 'jenis_ptk', className: 'gtk-col-role align-middle' },
            { data: 'status_summary', name: 'status_summary', orderable: false, searchable: false, className: 'gtk-col-status align-middle' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'gtk-col-actions align-middle' }
        ],
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>',
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 - 0 dari 0 data",
            zeroRecords: "Data tidak ditemukan",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        }
    });

    const reloadGtkTable = function(resetPaging = true) {
        window.clearTimeout(filterReloadTimer);
        $gtkFilterStatus
            .addClass('is-loading')
            .html('<i class="fas fa-circle-notch fa-spin mr-1"></i> Menyiapkan filter...');

        filterReloadTimer = window.setTimeout(function() {
            gtkTable.ajax.reload(null, resetPaging);
        }, 140);
    };

    // Filter functionality - Cascading Kategori PTK -> Jenis PTK
    const filterJenisPtkOptions = {
        'Pendidik': [
            { value: 'Guru Mapel', text: 'Guru Mapel' },
            { value: 'Guru BK', text: 'Guru BK' }
        ],
        'Tenaga Kependidikan': [
            { value: 'Kepala TU', text: 'Kepala TU' },
            { value: 'Staff TU', text: 'Staff TU' },
            { value: 'Bendahara', text: 'Bendahara' },
            { value: 'Laboran', text: 'Laboran' },
            { value: 'Pustakawan', text: 'Pustakawan' },
            { value: 'Cleaning Service', text: 'Cleaning Service' },
            { value: 'Satpam', text: 'Satpam' },
            { value: 'Lainnya', text: 'Lainnya' }
        ]
    };

    $('#filterKategoriPtk').on('change', function() {
        const kategori = $(this).val();
        const filterJenisPtk = $('#filterJenisPtk');
        // Reset jenis_ptk filter
        filterJenisPtk.empty();
        filterJenisPtk.append('<option value="">Semua</option>');
        
        if (kategori && filterJenisPtkOptions[kategori]) {
            filterJenisPtkOptions[kategori].forEach(function(option) {
                filterJenisPtk.append(`<option value="${option.value}">${option.text}</option>`);
            });
        } else {
            // If no kategori selected, show all jenis options
            Object.values(filterJenisPtkOptions).flat().forEach(function(option) {
                filterJenisPtk.append(`<option value="${option.value}">${option.text}</option>`);
            });
        }
        
        reloadGtkTable();
    });

    $('#filterJenisPtk, #filterJenisKelamin, #filterStatusKepegawaian, #filterStatus, #filterStatusAktif').on('change', function() {
        reloadGtkTable();
    });

    $('#btnResetFilter').on('click', function() {
        $('#filterKategoriPtk').val('');
        $('#filterJenisPtk').empty().append('<option value="">Semua</option>');
        // Repopulate all jenis options
        Object.values(filterJenisPtkOptions).flat().forEach(function(option) {
            $('#filterJenisPtk').append(`<option value="${option.value}">${option.text}</option>`);
        });
        $('#filterJenisKelamin').val('');
        $('#filterStatusKepegawaian').val('');
        $('#filterStatus').val('');
        $('#filterStatusAktif').val('1');
        reloadGtkTable();
    });

    $('#btnBulkSyncKemenag').on('click', async function() {
        const $button = $(this);

        try {
            $button.prop('disabled', true);

            const candidateResponse = await $.ajax({
                url: '{{ route('admin.gtk.sync-kemenag-candidates') }}',
                type: 'GET'
            });

            if (!candidateResponse.success || !candidateResponse.total) {
                Swal.fire({
                    icon: 'info',
                    title: 'Tidak Ada Data',
                    text: candidateResponse.message || 'Belum ada GTK ber-NIP yang bisa disinkronkan.'
                });
                return;
            }

            const candidates = candidateResponse.candidates || [];
            const previewNames = candidates.slice(0, 5).map(item => `<li>${escapeHtml(item.nama_lengkap)} <span class="text-muted">(${escapeHtml(item.nip)})</span></li>`).join('');
            const moreText = candidates.length > 5
                ? `<div class="mt-2 text-muted small">Dan ${candidates.length - 5} GTK lainnya akan diproses satu per satu.</div>`
                : '';

            const confirmResult = await Swal.fire({
                title: 'Sinkron Semua GTK Ber-NIP?',
                html: `
                    <div class="text-left">
                        <p class="mb-2">Sistem akan menyinkronkan <strong>${candidateResponse.total}</strong> GTK yang sudah memiliki NIP.</p>
                        <div class="alert alert-info py-2 px-3 mb-2">
                            <i class="fas fa-shield-alt mr-1"></i>
                            Mode aman aktif: proses ini hanya mengambil dan menyimpan hasil perbandingan dari Kemenag. Data lokal tidak akan diubah otomatis.
                        </div>
                        <div class="small text-muted mb-2">Contoh data yang akan diproses:</div>
                        <ul class="pl-3 mb-0">${previewNames}</ul>
                        ${moreText}
                    </div>
                `,
                icon: 'question',
                width: 680,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-sync-alt"></i> Ya, Mulai Sinkronisasi',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#6b7280'
            });

            if (!confirmResult.isConfirmed) {
                return;
            }

            await runBulkSyncKemenag(candidates, gtkTable);
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Menyiapkan Sinkronisasi',
                text: error?.responseJSON?.message || error?.message || 'Terjadi kesalahan saat menyiapkan data sinkronisasi.'
            });
        } finally {
            $button.prop('disabled', false);
        }
    });

    // Add GTK Form Submit
    $('#addGtkForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear previous errors
        $('.invalid-feedback').text('');
        
        $.ajax({
            url: '{{ route('admin.gtk.store') }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#addGtkModal').modal('hide');
                $('#addGtkForm')[0].reset();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message,
                    timer: 3000
                });
                
                gtkTable.ajax.reload();
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    for (let field in errors) {
                        $('#error-' + field).text(errors[field][0]);
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                    });
                }
            }
        });
    });

    // NIK input validation (only numbers, max 16)
    $('#nik').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').substring(0, 16);
    });

    // Cascading Dropdown: Kategori PTK â†’ Jenis PTK
    const jenisPtkOptions = {
        'Pendidik': [
            { value: 'Guru Mapel', text: 'Guru Mata Pelajaran' },
            { value: 'Guru BK', text: 'Guru BK (Bimbingan Konseling)' }
        ],
        'Tenaga Kependidikan': [
            { value: 'Kepala TU', text: 'Kepala Tata Usaha' },
            { value: 'Staff TU', text: 'Staff Tata Usaha' },
            { value: 'Bendahara', text: 'Bendahara' },
            { value: 'Laboran', text: 'Laboran' },
            { value: 'Pustakawan', text: 'Pustakawan' },
            { value: 'Cleaning Service', text: 'Cleaning Service' },
            { value: 'Satpam', text: 'Satpam' },
            { value: 'Lainnya', text: 'Lainnya' }
        ]
    };

    $('#kategori_ptk').on('change', function() {
        const kategori = $(this).val();
        const jenisPtkSelect = $('#jenis_ptk');
        
        jenisPtkSelect.empty();
        jenisPtkSelect.prop('disabled', true);
        
        if (kategori && jenisPtkOptions[kategori]) {
            jenisPtkSelect.prop('disabled', false);
            jenisPtkSelect.append('<option value="">Pilih Jenis PTK</option>');
            
            jenisPtkOptions[kategori].forEach(function(option) {
                jenisPtkSelect.append(`<option value="${option.value}">${option.text}</option>`);
            });
        } else {
            jenisPtkSelect.append('<option value="">Pilih Kategori PTK terlebih dahulu</option>');
        }
    });
});

async function runBulkSyncKemenag(candidates, gtkTable) {
    const stats = {
        total: candidates.length,
        processed: 0,
        success: 0,
        failed: 0,
        changed: 0,
        unchanged: 0,
    };

    showBulkSyncOverlay();
    resetBulkSyncOverlay(stats.total);
    addBulkSyncLog('Memulai sinkronisasi massal GTK ber-NIP...', 'info');

    for (const candidate of candidates) {
        updateBulkSyncCurrent(candidate, stats);

        try {
            const response = await $.ajax({
                url: `/admin/gtk/${candidate.id}/sync-kemenag`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                }
            });

            stats.processed++;

            if (response.success) {
                stats.success++;

                if (response.has_differences) {
                    stats.changed++;
                    addBulkSyncLog(`${candidate.nama_lengkap}: sinkron berhasil, ditemukan ${response.applicable_differences_count ?? response.differences_count ?? 0} perubahan yang bisa ditinjau.`, 'success');
                } else {
                    stats.unchanged++;
                    addBulkSyncLog(`${candidate.nama_lengkap}: sinkron berhasil, tidak ada perubahan data.`, 'neutral');
                }
            } else {
                stats.failed++;
                addBulkSyncLog(`${candidate.nama_lengkap}: ${response.message || 'sinkronisasi gagal.'}`, 'danger');
            }
        } catch (error) {
            stats.processed++;
            stats.failed++;
            addBulkSyncLog(`${candidate.nama_lengkap}: ${error?.responseJSON?.message || 'terjadi kesalahan saat menghubungi server.'}`, 'danger');
        }

        renderBulkSyncStats(stats);

        if (BULK_SYNC_DELAY_MS > 0 && stats.processed < stats.total) {
            await wait(BULK_SYNC_DELAY_MS);
        }
    }

    updateBulkSyncCurrent(null, stats, 'Sinkronisasi selesai. Menyiapkan ringkasan hasil...');
    gtkTable.ajax.reload(null, false);

    await wait(250);
    hideBulkSyncOverlay();

    await Swal.fire({
        icon: stats.failed > 0 ? 'warning' : 'success',
        title: stats.failed > 0 ? 'Sinkronisasi Selesai Dengan Catatan' : 'Sinkronisasi Selesai',
        html: `
            <div class="text-left">
                <div class="row text-center mb-3">
                    <div class="col-6 col-md-3 mb-2"><div class="border rounded py-2"><div class="small text-muted">Total</div><div class="font-weight-bold">${stats.total}</div></div></div>
                    <div class="col-6 col-md-3 mb-2"><div class="border rounded py-2"><div class="small text-muted">Berhasil</div><div class="font-weight-bold text-success">${stats.success}</div></div></div>
                    <div class="col-6 col-md-3 mb-2"><div class="border rounded py-2"><div class="small text-muted">Perubahan</div><div class="font-weight-bold text-info">${stats.changed}</div></div></div>
                    <div class="col-6 col-md-3 mb-2"><div class="border rounded py-2"><div class="small text-muted">Gagal</div><div class="font-weight-bold text-danger">${stats.failed}</div></div></div>
                </div>
                <p class="mb-0">Hasil sinkronisasi sudah tersimpan per GTK. Kamu bisa membuka detail GTK tertentu untuk meninjau perubahan sebelum menerapkannya ke data lokal.</p>
            </div>
        `,
        confirmButtonText: 'Tutup'
    });
}

function showBulkSyncOverlay() {
    $('#bulkSyncOverlay').removeClass('d-none');
    $('body').addClass('overflow-hidden');
}

function hideBulkSyncOverlay() {
    $('#bulkSyncOverlay').addClass('d-none');
    $('body').removeClass('overflow-hidden');
}

function resetBulkSyncOverlay(total) {
    $('#bulkSyncProgressText').text(`0 / ${total}`);
    $('#bulkSyncSuccessCount').text('0');
    $('#bulkSyncChangedCount').text('0');
    $('#bulkSyncFailedCount').text('0');
    $('#bulkSyncProgressBar')
        .css('width', '0%')
        .text('0%');
    $('#bulkSyncCurrentLabel').text('Menyiapkan sinkronisasi...');
    $('#bulkSyncLogList').html('<li class="list-group-item text-muted">Belum ada aktivitas yang diproses.</li>');
}

function renderBulkSyncStats(stats) {
    const percent = stats.total > 0 ? Math.round((stats.processed / stats.total) * 100) : 0;

    $('#bulkSyncProgressText').text(`${stats.processed} / ${stats.total}`);
    $('#bulkSyncSuccessCount').text(stats.success);
    $('#bulkSyncChangedCount').text(stats.changed);
    $('#bulkSyncFailedCount').text(stats.failed);
    $('#bulkSyncProgressBar')
        .css('width', `${percent}%`)
        .text(`${percent}%`);
}

function updateBulkSyncCurrent(candidate, stats, customText = null) {
    if (customText) {
        $('#bulkSyncCurrentLabel').text(customText);
        return;
    }

    const currentNumber = Math.min(stats.processed + 1, stats.total);
    $('#bulkSyncCurrentLabel').text(`Memproses ${currentNumber}/${stats.total}: ${candidate.nama_lengkap} (${candidate.nip})`);
}

function addBulkSyncLog(message, type = 'info') {
    const colorClass = {
        success: 'text-success',
        danger: 'text-danger',
        neutral: 'text-secondary',
        info: 'text-primary',
    }[type] || 'text-primary';

    const iconClass = {
        success: 'fa-check-circle',
        danger: 'fa-times-circle',
        neutral: 'fa-minus-circle',
        info: 'fa-info-circle',
    }[type] || 'fa-info-circle';

    const $list = $('#bulkSyncLogList');
    const emptyState = $list.find('.text-muted').length === 1 && $list.children().length === 1;

    if (emptyState) {
        $list.empty();
    }

    const timestamp = new Date().toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });

    $list.prepend(`
        <li class="list-group-item">
            <div class="d-flex align-items-start">
                <i class="fas ${iconClass} ${colorClass} mt-1 mr-2"></i>
                <div>
                    <div>${escapeHtml(message)}</div>
                    <div class="small text-muted mt-1">${timestamp}</div>
                </div>
            </div>
        </li>
    `);

    if ($list.children().length > 8) {
        $list.children().last().remove();
    }
}

function wait(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

function escapeHtml(text) {
    return String(text ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// Show GTK Detail
function handleGtkAction(item) {
    const action = item.dataset.action;
    const menu = item.closest('.simansa-gtk-action-menu');
    if (!menu) return;

    const gtkId = menu.dataset.gtkId;

    if (action === 'view') {
        showGtk(gtkId);
    } else if (action === 'schedule') {
        window.location.href = menu.dataset.scheduleUrl;
    } else if (action === 'workload') {
        window.location.href = menu.dataset.workloadUrl;
    } else if (action === 'mutation') {
        window.location.href = menu.dataset.mutationUrl;
    } else if (action === 'edit') {
        window.location.href = menu.dataset.editUrl;
    } else if (action === 'update-photo') {
        openGtkPhotoModal(menu);
    } else if (action === 'reset-password') {
        resetPassword(gtkId);
    } else if (action === 'login-as') {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = menu.dataset.loginUrl;
        form.target = '_blank';
        form.setAttribute('data-no-overlay', '');
        form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">';
        document.body.appendChild(form);
        form.submit();
        form.remove();
    } else if (action === 'delete') {
        deleteGtk(gtkId);
    }
}

function openGtkPhotoModal(menu) {
    gtkPhotoContext = {
        gtkId: menu.dataset.gtkId,
        name: menu.dataset.gtkName,
        uploadUrl: menu.dataset.uploadUrl
    };

    destroyGtkPhotoCropper();
    $('#gtkPhotoGtkName').text(gtkPhotoContext.name || '-');
    $('#gtkPhotoInput').val('');
    $('#gtkPhotoCropperWrap, #gtkPhotoProgress').addClass('d-none');
    $('#gtkPhotoDropzone').removeClass('d-none is-dragging');
    $('#gtkPhotoSave').prop('disabled', true).html('<i class="fas fa-save mr-1"></i>Crop, Kompres & Simpan');
    $('#gtkPhotoProgressBar').css('width', '0%');

    if (menu.dataset.photoUrl) {
        $('#gtkPhotoCurrent').attr('src', menu.dataset.photoUrl).show();
        $('#gtkPhotoCurrentEmpty').hide();
    } else {
        $('#gtkPhotoCurrent').attr('src', '').hide();
        $('#gtkPhotoCurrentEmpty').show();
    }

    $('#gtkPhotoModal').modal('show');
}

function destroyGtkPhotoCropper() {
    if (gtkPhotoCropper) {
        gtkPhotoCropper.destroy();
        gtkPhotoCropper = null;
    }
    $('#gtkPhotoCropImage').attr('src', '');
}

function formatGtkPhotoBytes(bytes) {
    if (!Number.isFinite(bytes) || bytes <= 0) return '0 KB';
    if (bytes >= 1024 * 1024) return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    return Math.ceil(bytes / 1024) + ' KB';
}

function prepareGtkPhoto(file) {
    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    const maxOriginalSize = 20 * 1024 * 1024;

    if (!allowedTypes.includes(file.type)) {
        Swal.fire({icon:'error', title:'Format Tidak Didukung', text:'Gunakan foto JPG, PNG, atau WEBP.'});
        return;
    }
    if (file.size > maxOriginalSize) {
        Swal.fire({icon:'error', title:'File Terlalu Besar', text:'Ukuran file asli maksimal 20 MB.'});
        return;
    }
    if (typeof Cropper === 'undefined') {
        Swal.fire({icon:'error', title:'Editor Foto Belum Siap', text:'Muat ulang halaman lalu coba kembali.'});
        return;
    }

    const reader = new FileReader();
    reader.onload = event => {
        destroyGtkPhotoCropper();
        $('#gtkPhotoDropzone').addClass('d-none');
        $('#gtkPhotoCropperWrap').removeClass('d-none');
        $('#gtkPhotoFileMeta').html(`<i class="fas fa-file-image text-primary mr-1"></i>${escapeHtml(file.name)} · ${formatGtkPhotoBytes(file.size)} <span class="text-success ml-1">akan dikompresi otomatis</span>`);

        const image = document.getElementById('gtkPhotoCropImage');
        image.onload = () => {
            gtkPhotoCropper = new Cropper(image, {
                aspectRatio: 4 / 5,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 0.92,
                responsive: true,
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                background: false,
                movable: true,
                zoomable: true,
                rotatable: true,
                scalable: false,
                ready: () => $('#gtkPhotoSave').prop('disabled', false)
            });
        };
        image.src = event.target.result;
    };
    reader.readAsDataURL(file);
}

function gtkPhotoCanvasToBlob(canvas, quality = 0.88) {
    return new Promise((resolve, reject) => {
        canvas.toBlob(blob => blob ? resolve(blob) : reject(new Error('Foto gagal dikompresi.')), 'image/jpeg', quality);
    });
}

async function compressedGtkPhotoBlob(canvas) {
    const targetBytes = 900 * 1024;
    let quality = 0.88;
    let blob = await gtkPhotoCanvasToBlob(canvas, quality);

    while (blob.size > targetBytes && quality > 0.6) {
        quality -= 0.07;
        blob = await gtkPhotoCanvasToBlob(canvas, quality);
    }

    return {blob, quality};
}

async function saveGtkPhoto() {
    if (!gtkPhotoContext || !gtkPhotoCropper) return;

    const $save = $('#gtkPhotoSave');
    const $progress = $('#gtkPhotoProgress');
    const $bar = $('#gtkPhotoProgressBar');
    const $label = $('#gtkPhotoProgressLabel');
    const $value = $('#gtkPhotoProgressValue');

    try {
        $save.prop('disabled', true).html('<i class="fas fa-circle-notch fa-spin mr-1"></i>Memproses…');
        $progress.removeClass('d-none');
        $label.text('Memotong dan mengompresi foto…');
        $value.text('0%');
        $bar.css('width', '0%');

        const canvas = gtkPhotoCropper.getCroppedCanvas({
            width: 720,
            height: 900,
            fillColor: '#ffffff',
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        });
        const compressed = await compressedGtkPhotoBlob(canvas);
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('foto_profile', compressed.blob, 'foto-profile-gtk.jpg');

        $label.text(`Mengupload hasil kompresi ${formatGtkPhotoBytes(compressed.blob.size)}…`);

        const response = await $.ajax({
            url: gtkPhotoContext.uploadUrl,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                const xhr = new XMLHttpRequest();
                xhr.upload.addEventListener('progress', event => {
                    if (!event.lengthComputable) return;
                    const percent = Math.round((event.loaded / event.total) * 100);
                    $bar.css('width', percent + '%');
                    $value.text(percent + '%');
                });
                return xhr;
            }
        });

        $bar.css('width', '100%');
        $value.text('100%');
        $label.text('Foto profil berhasil diperbarui.');
        $('#gtk-table').DataTable().ajax.reload(null, false);

        await Swal.fire({
            icon: 'success',
            title: 'Foto Profil Diperbarui',
            html: `${escapeHtml(response.message)}<br><small class="text-muted">Ukuran tersimpan ${response.compressed_size_kb} KB · ${response.width} × ${response.height} px</small>`,
            timer: 2400,
            showConfirmButton: false
        });
        $('#gtkPhotoModal').modal('hide');
    } catch (error) {
        const errors = error?.responseJSON?.errors;
        const message = errors ? Object.values(errors).flat().join(' · ') : (error?.responseJSON?.message || error?.message || 'Foto gagal disimpan.');
        $label.text('Upload gagal.');
        $bar.addClass('bg-danger').css('width', '100%');
        Swal.fire({icon:'error', title:'Gagal Memperbarui Foto', text:message});
        $save.prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Crop, Kompres & Simpan');
    }
}

$(function() {
    const $dropzone = $('#gtkPhotoDropzone');
    const $input = $('#gtkPhotoInput');
    const openGtkPhotoBrowser = () => {
        const input = $input.get(0);
        if (!input) return;
        input.value = '';
        input.click();
    };

    $dropzone.on('click', openGtkPhotoBrowser)
        .on('keydown', event => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openGtkPhotoBrowser();
            }
        })
        .on('dragenter dragover', function(event) {
            event.preventDefault();
            $(this).addClass('is-dragging');
        })
        .on('dragleave drop', function(event) {
            event.preventDefault();
            $(this).removeClass('is-dragging');
        })
        .on('drop', event => {
            const file = event.originalEvent.dataTransfer.files?.[0];
            if (file) prepareGtkPhoto(file);
        });

    $input.on('change', function() {
        if (this.files?.[0]) prepareGtkPhoto(this.files[0]);
    });
    $('#gtkPhotoChooseAgain').on('click', openGtkPhotoBrowser);
    $('#gtkPhotoSave').on('click', saveGtkPhoto);
    $('[data-photo-control]').on('click', function() {
        if (!gtkPhotoCropper) return;
        const action = this.dataset.photoControl;
        if (action === 'zoom-in') gtkPhotoCropper.zoom(0.1);
        if (action === 'zoom-out') gtkPhotoCropper.zoom(-0.1);
        if (action === 'rotate-left') gtkPhotoCropper.rotate(-90);
        if (action === 'rotate-right') gtkPhotoCropper.rotate(90);
    });
    $('#gtkPhotoModal').on('hidden.bs.modal', () => {
        destroyGtkPhotoCropper();
        gtkPhotoContext = null;
        $('#gtkPhotoProgressBar').removeClass('bg-danger');
    });
});

function showGtk(id) {
    $('#viewGtkModal').modal('show');
    $('#viewGtkContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i><p>Loading...</p></div>');
    
    $.ajax({
        url: '/admin/gtk/' + id,
        type: 'GET',
        success: function(response) {
            const gtk = response.data;
            const photoUrl = gtk.foto_profile_url || '';
            let html = `
                <div class="row align-items-stretch">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <aside class="gtk-detail-profile">
                            <div class="gtk-detail-profile__photo"><img src="${photoUrl}" alt="Foto ${gtk.nama_lengkap}" onerror="this.closest('.gtk-detail-profile__photo').innerHTML='<i class=\'fas fa-user text-primary fa-3x mt-5\'></i>'"></div>
                            <div class="gtk-detail-profile__name">${gtk.nama_lengkap}</div>
                            <div class="gtk-detail-profile__meta">${gtk.jenis_ptk || 'GTK'}${gtk.jabatan ? ' · ' + gtk.jabatan : ''}</div>
                        </aside>
                    </div>
                    <div class="col-md-8">
                        <h5 class="border-bottom pb-2">Data Pribadi</h5>
                        <table class="table table-sm">
                            <tr><th width="150">Nama Lengkap</th><td>${gtk.nama_lengkap}</td></tr>
                            <tr><th>NIK</th><td>${gtk.nik}</td></tr>
                            <tr><th>NUPTK</th><td>${gtk.nuptk || '-'}</td></tr>
                            <tr><th>NIP</th><td>${gtk.nip || '-'}</td></tr>
                            <tr><th>Kode GTK Jadwal Wakakur</th><td><code>${gtk.kode_gtk || '-'}</code></td></tr>
                            <tr><th>Jenis Kelamin</th><td>${gtk.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan'}</td></tr>
                            <tr><th>Tempat, Tgl Lahir</th><td>${gtk.tempat_lahir || '-'}, ${gtk.tanggal_lahir || '-'}</td></tr>
                            <tr><th>Email</th><td>${gtk.email || '-'}</td></tr>
                            <tr><th>No HP</th><td>${gtk.nomor_hp || '-'}</td></tr>
                        </table>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2">Data Kepegawaian</h5>
                        <table class="table table-sm">
                            <tr><th width="150">Kategori PTK</th><td>${gtk.kategori_ptk ? '<span class="badge badge-' + (gtk.kategori_ptk === 'Pendidik' ? 'primary' : 'info') + '">' + gtk.kategori_ptk + '</span>' : '-'}</td></tr>
                            <tr><th>Jenis PTK</th><td>${gtk.jenis_ptk || '-'}</td></tr>
                            <tr><th>Status Kepegawaian</th><td>${gtk.status_kepegawaian || '-'}</td></tr>
                            <tr><th>Jabatan</th><td>${gtk.jabatan || '-'}</td></tr>
                            <tr><th>TMT Kerja</th><td>${gtk.tmt_kerja || '-'}</td></tr>
                        </table>
                        
                        <h5 class="border-bottom pb-2 mt-3">Alamat</h5>
                        <table class="table table-sm">
                            <tr><th width="150">Alamat</th><td>${gtk.alamat || '-'}</td></tr>
                            <tr><th>RT/RW</th><td>${gtk.rt || '-'} / ${gtk.rw || '-'}</td></tr>
                            <tr><th>Kelurahan</th><td>${gtk.kelurahan?.name || '-'}</td></tr>
                            <tr><th>Kecamatan</th><td>${gtk.kecamatan?.name || '-'}</td></tr>
                            <tr><th>Kabupaten</th><td>${gtk.kabupaten?.name || '-'}</td></tr>
                            <tr><th>Provinsi</th><td>${gtk.provinsi?.name || '-'}</td></tr>
                            <tr><th>Kode Pos</th><td>${gtk.kodepos || '-'}</td></tr>
                        </table>
                    </div>
                </div>
            `;
            $('#viewGtkContent').html(html);
        },
        error: function() {
            $('#viewGtkContent').html('<div class="alert alert-danger">Gagal memuat data</div>');
        }
    });
}

// Reset Password
function resetPassword(id) {
    Swal.fire({
        title: 'Reset Password?',
        text: 'Password akan direset menjadi NIK',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Reset!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/admin/gtk/' + id + '/reset-password',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        timer: 2000
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                    });
                }
            });
        }
    });
}

// Delete GTK
function deleteGtk(id) {
    Swal.fire({
        title: 'Hapus GTK?',
        text: 'Data GTK dan akun user akan dihapus permanen!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/admin/gtk/' + id,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        timer: 2000
                    });
                    
                    $('#gtk-table').DataTable().ajax.reload();
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                    });
                }
            });
        }
    });
}
</script>
@stop
