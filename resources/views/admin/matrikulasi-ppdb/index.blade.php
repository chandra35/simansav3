@extends('adminlte::page')

@section('title', 'Matrikulasi PPDB')

@section('plugins.Select2', true)
@section('plugins.Sweetalert2', true)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-7">
            <h1><i class="fas fa-user-plus"></i> Matrikulasi PPDB</h1>
        </div>
        <div class="col-sm-5">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Matrikulasi PPDB</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-filter"></i> Tujuan Matrikulasi</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="tahun_pelajaran_id">Tahun Pelajaran</label>
                        <select id="tahun_pelajaran_id" class="form-control">
                            @foreach($tahunPelajaran as $tp)
                                <option value="{{ $tp->id }}" @selected($selectedTahunId === $tp->id)>
                                    {{ $tp->nama }} {{ $tp->is_active ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="kelas_id">Kelas Matrikulasi</label>
                        <select id="kelas_id" class="form-control">
                            <option value="">Pilih kelas matrikulasi</option>
                            @foreach($kelasMatrikulasi as $kelas)
                                <option value="{{ $kelas->id }}">
                                    {{ $kelas->nama_lengkap ?? $kelas->nama_kelas }} - {{ $kelas->tahunPelajaran?->nama }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">
                            Buat kelas dengan jenis <strong>Matrikulasi PPDB</strong> di menu Manajemen Kelas jika daftar ini kosong.
                        </small>
                    </div>

                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="include_documents" checked>
                        <label class="custom-control-label" for="include_documents">Salin dokumen PPDB ke SIMANSA</label>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-shield-alt"></i> Pengaman</h3>
                </div>
                <div class="card-body small text-muted">
                    <p class="mb-2">Import hanya mengambil pendaftar yang lulus dan sudah punya data registrasi komite.</p>
                    <p class="mb-2">Tahun ajaran PPDB harus cocok dengan tahun kelas matrikulasi.</p>
                    <p class="mb-0">Dokumen disimpan ulang ke storage dokumen SIMANSA dengan jejak asal PPDB.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-search"></i> Ambil Data Dari PPDB</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="calon_siswa_ids">Pendaftar PPDB</label>
                        <select id="calon_siswa_ids" class="form-control" multiple></select>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <button type="button" class="btn btn-secondary mr-2" id="btnPreview">
                            <i class="fas fa-eye"></i> Preview
                        </button>
                        <button type="button" class="btn btn-primary" id="btnImport" disabled>
                            <i class="fas fa-sync-alt"></i> Import ke Matrikulasi
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped" id="previewTable">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>NISN</th>
                                    <th>No. Reg</th>
                                    <th>Tahun PPDB</th>
                                    <th>Jurusan</th>
                                    <th>Dok.</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Pilih pendaftar lalu klik Preview.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="resultBox" class="mt-3" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .select2-container--default .select2-selection--multiple {
            min-height: 42px;
        }
        .status-chip {
            display: inline-flex;
            align-items: center;
            padding: .15rem .45rem;
            border-radius: .35rem;
            font-size: .75rem;
            font-weight: 700;
        }
        .status-baru { background: #e8f5e9; color: #1b5e20; }
        .status-sudah_ada { background: #e3f2fd; color: #0d47a1; }
        .status-konflik { background: #ffebee; color: #b71c1c; }
    </style>
@stop

@section('js')
    <script>
        const routes = {
            candidates: @json(route('admin.matrikulasi-ppdb.candidates')),
            preview: @json(route('admin.matrikulasi-ppdb.preview')),
            import: @json(route('admin.matrikulasi-ppdb.import')),
        };

        function selectedIds() {
            return $('#calon_siswa_ids').val() || [];
        }

        function statusChip(status) {
            const label = {
                baru: 'Baru',
                sudah_ada: 'Sudah Ada',
                konflik: 'Konflik',
            }[status] || status;

            return `<span class="status-chip status-${status}">${label}</span>`;
        }

        function renderPreview(rows) {
            const $tbody = $('#previewTable tbody');
            if (!rows.length) {
                $tbody.html('<tr><td colspan="7" class="text-center text-muted">Tidak ada data preview.</td></tr>');
                $('#btnImport').prop('disabled', true);
                return;
            }

            const html = rows.map(row => `
                <tr>
                    <td><strong>${row.nama_lengkap || '-'}</strong><br><small class="text-muted">${row.nik || '-'}</small></td>
                    <td>${row.nisn || '-'}</td>
                    <td>${row.nomor_registrasi || row.nomor_tes || '-'}</td>
                    <td>${row.tahun_ppdb || '-'}</td>
                    <td>${row.jurusan_final || row.jurusan_awal || '-'}</td>
                    <td class="text-center">${row.documents_count || 0}</td>
                    <td>${statusChip(row.import_status)}</td>
                </tr>
            `).join('');

            $tbody.html(html);
            $('#btnImport').prop('disabled', rows.some(row => row.import_status === 'konflik'));
        }

        function showResult(result) {
            const rows = (result.items || []).map(item => `
                <tr class="${item.status === 'success' ? 'table-success' : 'table-danger'}">
                    <td>${item.nama}</td>
                    <td>${item.nisn}</td>
                    <td>${item.message}</td>
                    <td class="text-center">${item.documents_copied || 0}</td>
                </tr>
            `).join('');

            $('#resultBox').show().html(`
                <div class="alert alert-info">
                    <strong>Hasil:</strong> ${result.success} berhasil, ${result.failed} gagal, ${result.documents_copied} dokumen disalin.
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead><tr><th>Nama</th><th>NISN</th><th>Pesan</th><th>Dok.</th></tr></thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            `);
        }

        $(function () {
            $.ajaxSetup({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || @json(csrf_token())}
            });

            $('#tahun_pelajaran_id').on('change', function () {
                const url = new URL(window.location.href);
                url.searchParams.set('tahun_pelajaran_id', this.value);
                window.location.href = url.toString();
            });

            $('#calon_siswa_ids').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Cari nama, NISN, nomor registrasi, atau nomor tes',
                minimumInputLength: 2,
                ajax: {
                    url: routes.candidates,
                    dataType: 'json',
                    delay: 300,
                    data: params => ({
                        q: params.term,
                        tahun_pelajaran_id: $('#tahun_pelajaran_id').val()
                    }),
                    processResults: data => data,
                    error: xhr => {
                        Swal.fire('Koneksi PPDB gagal', xhr.responseJSON?.message || 'Tidak bisa mengambil data dari PPDB.', 'error');
                    }
                },
                templateResult: function (item) {
                    if (!item.id) return item.text;
                    return $(`<div><strong>${item.text}</strong><br><small>${item.tahun || '-'} | Dokumen: ${item.documents_count || 0} | ${item.status || '-'}</small></div>`);
                }
            });

            $('#btnPreview').on('click', function () {
                const ids = selectedIds();
                if (!ids.length) {
                    Swal.fire('Belum ada pendaftar', 'Pilih minimal satu pendaftar PPDB.', 'warning');
                    return;
                }

                $.post(routes.preview, {
                    calon_siswa_ids: ids,
                    tahun_pelajaran_id: $('#tahun_pelajaran_id').val()
                }).done(response => {
                    renderPreview(response.data || []);
                }).fail(xhr => {
                    Swal.fire('Preview gagal', xhr.responseJSON?.message || 'Gagal membuat preview.', 'error');
                });
            });

            $('#btnImport').on('click', function () {
                const ids = selectedIds();
                const kelasId = $('#kelas_id').val();
                if (!ids.length || !kelasId) {
                    Swal.fire('Data belum lengkap', 'Pilih pendaftar dan kelas matrikulasi tujuan.', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Import ke matrikulasi?',
                    text: 'Data siswa dan dokumen yang dipilih akan disimpan ke SIMANSA.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, import',
                    cancelButtonText: 'Batal'
                }).then(result => {
                    if (!result.isConfirmed) return;

                    $('#btnImport').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengimport...');
                    $.post(routes.import, {
                        calon_siswa_ids: ids,
                        tahun_pelajaran_id: $('#tahun_pelajaran_id').val(),
                        kelas_id: kelasId,
                        include_documents: $('#include_documents').is(':checked') ? 1 : 0
                    }).done(response => {
                        showResult(response.data || {});
                        Swal.fire('Selesai', response.message || 'Import selesai.', 'success');
                    }).fail(xhr => {
                        Swal.fire('Import gagal', xhr.responseJSON?.message || 'Gagal import.', 'error');
                    }).always(() => {
                        $('#btnImport').prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Import ke Matrikulasi');
                    });
                });
            });
        });
    </script>
@stop
