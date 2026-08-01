@extends('adminlte::page')

@section('title', 'Data Lulusan')

@section('content_header')
    <h1>Data Lulusan</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Input Data Lanjut Studi</h3>
                </div>
                <form action="{{ route('siswa.lulusan.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h5><i class="icon fas fa-info-circle"></i> Informasi</h5>
                            Data ini dipakai untuk tracking lulusan. Silakan isi sesuai hasil diterima yang sudah final.
                        </div>

                        <div class="form-group">
                            <label>Tahun Pelajaran</label>
                            <input type="text" class="form-control" value="{{ $targetTahunPelajaran->nama }}" readonly>
                        </div>

                        <div class="form-group">
                            <label>Kelas Acuan</label>
                            <input type="text" class="form-control" value="{{ (optional($targetSiswaKelas->kelas)->nama_lengkap ?? optional($targetSiswaKelas->kelas)->nama_kelas ?? '-') . (optional($targetSiswaKelas->kelas)->asrama_suffix ?? '') }}" readonly>
                        </div>

                        <div class="form-group">
                            <label for="jalur_masuk">Jalur Diterima</label>
                            <select name="jalur_masuk" id="jalur_masuk" class="form-control @error('jalur_masuk') is-invalid @enderror" required>
                                <option value="">Pilih Jalur Diterima</option>
                                @foreach($jalurMasukOptions as $jalur)
                                    <option value="{{ $jalur }}" {{ old('jalur_masuk', $dataLulusan->jalur_masuk) === $jalur ? 'selected' : '' }}>
                                        {{ $jalur }}
                                    </option>
                                @endforeach
                            </select>
                            @error('jalur_masuk')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($snbpRegistration)
                            <div class="alert alert-info {{ old('jalur_masuk', $dataLulusan->jalur_masuk) === 'SNBP' ? '' : 'd-none' }}" id="snbpRegistrationNotice">
                                <i class="fas fa-link mr-1"></i>
                                Data nomor pendaftaran SNBP terdeteksi:
                                <strong>{{ $snbpRegistration->nomor_pendaftaran }}</strong>.
                                Jika memilih jalur <strong>SNBP</strong>, data lulusan ini akan otomatis ditautkan ke registrasi SNBP Anda.
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="nama_universitas">Nama Universitas / Kampus</label>
                            <input type="hidden" name="referensi_perguruan_tinggi_id" id="referensi_perguruan_tinggi_id" value="{{ old('referensi_perguruan_tinggi_id', $dataLulusan->referensi_perguruan_tinggi_id) }}">
                            <div class="position-relative">
                                <input type="text" name="nama_universitas" id="nama_universitas" autocomplete="off" class="form-control @error('nama_universitas') is-invalid @enderror" value="{{ old('nama_universitas', $dataLulusan->nama_universitas) }}" required>
                                <div id="kampusSuggestions" class="list-group position-absolute w-100 shadow-sm" style="z-index: 1050; display: none;"></div>
                            </div>
                            <small class="text-muted">Ketik minimal 2 huruf untuk melihat saran kampus. Jika kampus belum ada, lanjutkan isi manual.</small>
                            @error('nama_universitas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="jurusan_fakultas">Jurusan / Fakultas <small class="text-muted">(Opsional)</small></label>
                            <input type="text" name="jurusan_fakultas" id="jurusan_fakultas" class="form-control @error('jurusan_fakultas') is-invalid @enderror" value="{{ old('jurusan_fakultas', $dataLulusan->jurusan_fakultas) }}">
                            <small class="text-muted">Boleh dikosongkan. Kolom ini akan terisi otomatis jika program studi berhasil dicocokkan dengan referensi kampus/prodi.</small>
                            @error('jurusan_fakultas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="program_studi">Program Studi</label>
                            <input type="hidden" name="referensi_program_studi_id" id="referensi_program_studi_id" value="{{ old('referensi_program_studi_id', $dataLulusan->referensi_program_studi_id) }}">
                            <div class="position-relative">
                                <input type="text" name="program_studi" id="program_studi" autocomplete="off" class="form-control @error('program_studi') is-invalid @enderror" value="{{ old('program_studi', $dataLulusan->program_studi) }}" required>
                                <div id="prodiSuggestions" class="list-group position-absolute w-100 shadow-sm" style="z-index: 1049; display: none;"></div>
                            </div>
                            <small class="text-muted">Program studi tetap wajib diisi. Pilih kampus dari saran terlebih dulu agar saran program studi sesuai kampus tersebut. Jika hasil SNBP belum menemukan fakultas, cukup isi program studi dan biarkan kolom jurusan/fakultas kosong.</small>
                            @error('program_studi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="keterangan">Keterangan Tambahan</label>
                            <textarea name="keterangan" id="keterangan" rows="4" class="form-control @error('keterangan') is-invalid @enderror" placeholder="Opsional">{{ old('keterangan', $dataLulusan->keterangan) }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Simpan Data Lulusan
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">Ringkasan</h3>
                </div>
                <div class="card-body">
                    <dl class="mb-0">
                        <dt>Nama Siswa</dt>
                        <dd>{{ $siswa->nama_lengkap }}</dd>

                        <dt>NISN</dt>
                        <dd>{{ $siswa->nisn }}</dd>

                        <dt>Status Data</dt>
                        <dd>
                            @if($dataLulusan->exists)
                                <span class="badge badge-success">Sudah Mengisi</span>
                            @else
                                <span class="badge badge-warning">Belum Mengisi</span>
                            @endif
                        </dd>

                        <dt>Nomor SNBP</dt>
                        <dd>{{ $snbpRegistration?->nomor_pendaftaran ?? '-' }}</dd>

                        <dt>Relasi SNBP</dt>
                        <dd>
                            @if($dataLulusan->snbp_registration_id)
                                <span class="badge badge-primary">Terhubung</span>
                            @else
                                <span class="badge badge-secondary">Belum Terhubung</span>
                            @endif
                        </dd>

                        <dt>Terakhir Diperbarui</dt>
                        <dd>{{ $dataLulusan->updated_at?->format('d M Y H:i') ?? '-' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        #kampusSuggestions .list-group-item {
            cursor: pointer;
        }

        #kampusSuggestions .kampus-jenis {
            font-size: 0.75rem;
        }

        #prodiSuggestions .list-group-item {
            cursor: pointer;
        }

        #prodiSuggestions .prodi-meta {
            font-size: 0.75rem;
        }
    </style>
@stop

@section('js')
    <script>
        $(function () {
            let xhr = null;
            let xhrProdi = null;
            const $input = $('#nama_universitas');
            const $hidden = $('#referensi_perguruan_tinggi_id');
            const $suggestions = $('#kampusSuggestions');
            const $prodiInput = $('#program_studi');
            const $prodiHidden = $('#referensi_program_studi_id');
            const $prodiSuggestions = $('#prodiSuggestions');
            const $fakultasInput = $('#jurusan_fakultas');
            const $jalurMasuk = $('#jalur_masuk');
            const $snbpNotice = $('#snbpRegistrationNotice');

            function hideSuggestions() {
                $suggestions.hide().empty();
            }

            function hideProdiSuggestions() {
                $prodiSuggestions.hide().empty();
            }

            function renderSuggestions(items) {
                if (!items.length) {
                    hideSuggestions();
                    return;
                }

                $suggestions.empty();

                items.forEach(item => {
                    $suggestions.append(`
                        <button type="button" class="list-group-item list-group-item-action kampus-suggestion" data-id="${item.id}" data-nama="${item.nama}">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>${item.nama}</span>
                                <span class="badge badge-info kampus-jenis">${item.jenis}</span>
                            </div>
                        </button>
                    `);
                });

                $suggestions.show();
            }

            function renderProdiSuggestions(items) {
                if (!items.length) {
                    hideProdiSuggestions();
                    return;
                }

                $prodiSuggestions.empty();

                items.forEach(item => {
                    const jenjang = item.jenjang ? `<span class="badge badge-success mr-1">${item.jenjang}</span>` : '';
                    const fakultas = item.fakultas ? `<span class="text-muted prodi-meta">${item.fakultas}</span>` : '';

                    $prodiSuggestions.append(`
                        <button type="button" class="list-group-item list-group-item-action prodi-suggestion" data-id="${item.id}" data-nama="${item.nama}" data-jenjang="${item.jenjang ?? ''}" data-fakultas="${item.fakultas ?? ''}">
                            <div>${jenjang}<span>${item.nama}</span></div>
                            ${fakultas}
                        </button>
                    `);
                });

                $prodiSuggestions.show();
            }

            function resetProdiReference(keepInputValue = true) {
                $prodiHidden.val('');
                if (!keepInputValue) {
                    $prodiInput.val('');
                }
                hideProdiSuggestions();
            }

            $input.on('input', function () {
                const query = $(this).val().trim();
                $hidden.val('');
                resetProdiReference(false);

                if (query.length < 2) {
                    hideSuggestions();
                    return;
                }

                if (xhr) {
                    xhr.abort();
                }

                xhr = $.ajax({
                    url: '{{ route('siswa.lulusan.referensi.search') }}',
                    data: { q: query },
                    success: function (response) {
                        renderSuggestions(response);
                    },
                    error: function () {
                        hideSuggestions();
                    }
                });
            });

            $(document).on('click', '.kampus-suggestion', function () {
                $input.val($(this).data('nama'));
                $hidden.val($(this).data('id'));
                resetProdiReference(false);
                hideSuggestions();
            });

            $prodiInput.on('input', function () {
                const query = $(this).val().trim();
                const campusId = $hidden.val();
                $prodiHidden.val('');

                if (query.length < 2 || !campusId) {
                    hideProdiSuggestions();
                    return;
                }

                if (xhrProdi) {
                    xhrProdi.abort();
                }

                xhrProdi = $.ajax({
                    url: '{{ route('siswa.lulusan.prodi.search') }}',
                    data: {
                        q: query,
                        referensi_perguruan_tinggi_id: campusId
                    },
                    success: function (response) {
                        renderProdiSuggestions(response);
                    },
                    error: function () {
                        hideProdiSuggestions();
                    }
                });
            });

            $(document).on('click', '.prodi-suggestion', function () {
                const jenjang = $(this).data('jenjang');
                const nama = $(this).data('nama');
                const fakultas = $(this).data('fakultas');
                const label = [jenjang, nama].filter(Boolean).join(' ').trim();

                $prodiInput.val(label);
                $prodiHidden.val($(this).data('id'));

                if (!$fakultasInput.val() && fakultas) {
                    $fakultasInput.val(fakultas);
                }

                hideProdiSuggestions();
            });

            $(document).on('click', function (event) {
                if (!$(event.target).closest('#nama_universitas, #kampusSuggestions').length) {
                    hideSuggestions();
                }

                if (!$(event.target).closest('#program_studi, #prodiSuggestions').length) {
                    hideProdiSuggestions();
                }
            });

            function updateSnbpNotice() {
                if (!$snbpNotice.length) {
                    return;
                }

                if ($jalurMasuk.val() === 'SNBP') {
                    $snbpNotice.removeClass('d-none');
                } else {
                    $snbpNotice.addClass('d-none');
                }
            }

            updateSnbpNotice();
            $jalurMasuk.on('change', updateSnbpNotice);
        });
    </script>
@stop
