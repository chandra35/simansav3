@php
    $kelasAktif = $siswa->kelasTahunAktif->first();
    $dash = '—';
@endphp

<div class="gtk-wali-student-detail">
    <div class="row align-items-center mb-3">
        <div class="col-auto">
            <img src="{{ $siswa->foto_profile_url }}" alt="Foto {{ $siswa->nama_lengkap }}" class="img-circle elevation-1" style="width:72px;height:72px;object-fit:cover;">
        </div>
        <div class="col pl-0">
            <h5 class="font-weight-bold mb-1">{{ $siswa->nama_lengkap }}</h5>
            <div class="text-muted mb-2">NISN {{ $siswa->nisn ?: $dash }} · {{ $kelasAktif?->nama_lengkap ?? $kelasAktif?->nama_kelas ?? $dash }}</div>
            <span class="badge {{ $siswa->data_diri_completed ? 'badge-success' : 'badge-warning' }}">Data Diri {{ $siswa->data_diri_completed ? 'Lengkap' : 'Belum' }}</span>
            <span class="badge {{ $siswa->data_ortu_completed ? 'badge-success' : 'badge-warning' }}">Data Ortu {{ $siswa->data_ortu_completed ? 'Lengkap' : 'Belum' }}</span>
        </div>
    </div>

    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#wali-ringkasan" role="tab"><i class="fas fa-user mr-1"></i> Data Siswa</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#wali-data-diri" role="tab"><i class="fas fa-id-card mr-1"></i> Data Diri</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#wali-data-ortu" role="tab"><i class="fas fa-users mr-1"></i> Orang Tua</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#wali-sekolah-asal" role="tab"><i class="fas fa-school mr-1"></i> Sekolah Asal</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#wali-dokumen" role="tab"><i class="fas fa-file-alt mr-1"></i> Dokumen</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#wali-catatan" role="tab"><i class="fas fa-sticky-note mr-1"></i> Catatan</a></li>
    </ul>

    <div class="tab-content pt-3">
        <div class="tab-pane fade show active" id="wali-ringkasan" role="tabpanel">
            <div class="row">
                <div class="col-lg-6">
                    <h6 class="text-primary font-weight-bold"><i class="fas fa-user mr-1"></i> Informasi Siswa</h6>
                    <div class="table-responsive"><table class="table table-sm table-bordered table-detail">
                        <tr><td class="bg-light" width="40%"><strong>NISN</strong></td><td>{{ $siswa->nisn ?: $dash }}</td></tr>
                        <tr><td class="bg-light"><strong>NIS Lokal</strong></td><td>{{ $siswa->nis_lokal ?: $dash }}</td></tr>
                        <tr><td class="bg-light"><strong>Nomor Tes PPDB</strong></td><td>{{ $siswa->nomor_tes ?: $dash }}</td></tr>
                        <tr><td class="bg-light"><strong>Nama Lengkap</strong></td><td>{{ $siswa->nama_lengkap }}</td></tr>
                        <tr><td class="bg-light"><strong>Jenis Kelamin</strong></td><td>{{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td></tr>
                        <tr><td class="bg-light"><strong>Kelas Aktif</strong></td><td>{{ $kelasAktif?->nama_lengkap ?? $kelasAktif?->nama_kelas ?? $dash }}</td></tr>
                        <tr><td class="bg-light"><strong>Tahun Masuk</strong></td><td>{{ $siswa->tahun_masuk ?: $dash }}</td></tr>
                        <tr><td class="bg-light"><strong>Asal Siswa</strong></td><td>{{ $siswa->asal_siswa ? \Illuminate\Support\Str::headline($siswa->asal_siswa) : $dash }}</td></tr>
                    </table></div>
                </div>
                <div class="col-lg-6">
                    <h6 class="text-primary font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Status Data</h6>
                    <div class="table-responsive"><table class="table table-sm table-bordered table-detail">
                        <tr><td class="bg-light" width="40%"><strong>Status Siswa</strong></td><td><span class="badge badge-success">{{ $siswa->status_siswa ? \Illuminate\Support\Str::headline($siswa->status_siswa) : 'Aktif' }}</span></td></tr>
                        <tr><td class="bg-light"><strong>Data Diri</strong></td><td><span class="badge {{ $siswa->data_diri_completed ? 'badge-success' : 'badge-warning' }}">{{ $siswa->data_diri_completed ? 'Lengkap' : 'Belum Lengkap' }}</span></td></tr>
                        <tr><td class="bg-light"><strong>Data Orang Tua</strong></td><td><span class="badge {{ $siswa->data_ortu_completed ? 'badge-success' : 'badge-warning' }}">{{ $siswa->data_ortu_completed ? 'Lengkap' : 'Belum Lengkap' }}</span></td></tr>
                        <tr><td class="bg-light"><strong>Status EMIS</strong></td><td><span class="badge {{ $siswa->emis_registered ? 'badge-success' : 'badge-warning' }}">{{ $siswa->emis_registered ? 'Sudah Masuk EMIS' : 'Belum Masuk EMIS' }}</span></td></tr>
                    </table></div>

                    <h6 class="text-primary font-weight-bold mt-3"><i class="fas fa-key mr-1"></i> Akun Login</h6>
                    <div class="table-responsive"><table class="table table-sm table-bordered table-detail">
                        <tr><td class="bg-light" width="40%"><strong>Username</strong></td><td>{{ $siswa->user?->username ?: $dash }}</td></tr>
                        <tr><td class="bg-light"><strong>Email</strong></td><td>{{ $siswa->user?->email ?: $dash }}</td></tr>
                        <tr><td class="bg-light"><strong>Status Login</strong></td><td>{{ $siswa->user ? ($siswa->user->is_first_login ? 'Belum ganti password' : 'Sudah aktif') : $dash }}</td></tr>
                    </table></div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="wali-data-diri" role="tabpanel">
            <div class="row">
                <div class="col-lg-6">
                    <h6 class="text-primary font-weight-bold"><i class="fas fa-id-card mr-1"></i> Data Pribadi</h6>
                    <div class="table-responsive"><table class="table table-sm table-bordered table-detail">
                        <tr><td class="bg-light" width="40%"><strong>NIK</strong></td><td>{{ $siswa->nik ?: $dash }}</td></tr>
                        <tr><td class="bg-light"><strong>Tempat Lahir</strong></td><td>{{ $siswa->tempat_lahir ?: $dash }}</td></tr>
                        <tr><td class="bg-light"><strong>Tanggal Lahir</strong></td><td>{{ $siswa->tanggal_lahir?->translatedFormat('d F Y') ?? $dash }}</td></tr>
                        <tr><td class="bg-light"><strong>Agama</strong></td><td>{{ $siswa->agama ?: $dash }}</td></tr>
                        <tr><td class="bg-light"><strong>Jumlah Saudara</strong></td><td>{{ $siswa->jumlah_saudara ?? $dash }}</td></tr>
                        <tr><td class="bg-light"><strong>Anak Ke</strong></td><td>{{ $siswa->anak_ke ?? $dash }}</td></tr>
                        <tr><td class="bg-light"><strong>Hobi</strong></td><td>{{ $siswa->hobi ?: $dash }}</td></tr>
                        <tr><td class="bg-light"><strong>Cita-cita</strong></td><td>{{ $siswa->cita_cita ?: $dash }}</td></tr>
                        <tr><td class="bg-light"><strong>Nomor PKH</strong></td><td>{{ $siswa->nomor_pkh ?: $dash }}</td></tr>
                        <tr><td class="bg-light"><strong>No. HP</strong></td><td>
                            @if($siswa->nomor_hp)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $siswa->nomor_hp) }}" data-no-overlay><i class="fas fa-phone-alt mr-1"></i>{{ $siswa->nomor_hp }}</a>@else{{ $dash }}@endif
                        </td></tr>
                    </table></div>
                </div>
                <div class="col-lg-6">
                    <h6 class="text-primary font-weight-bold"><i class="fas fa-map-marker-alt mr-1"></i> Tempat Tinggal</h6>
                    <div class="table-responsive"><table class="table table-sm table-bordered table-detail">
                        <tr><td class="bg-light" width="40%"><strong>Jenis Tinggal</strong></td><td>{{ $siswa->jenis_tempat_tinggal ?: $dash }}</td></tr>
                        <tr><td class="bg-light"><strong>Alamat Sama Ortu</strong></td><td>{{ $siswa->alamat_sama_ortu ? 'Ya' : 'Tidak' }}</td></tr>
                        <tr><td class="bg-light"><strong>Alamat</strong></td><td>{{ $siswa->getAlamatLengkapSiswa() ?: $dash }}</td></tr>
                        <tr><td class="bg-light"><strong>RT / RW</strong></td><td>{{ $siswa->rt_siswa ?: $dash }} / {{ $siswa->rw_siswa ?: $dash }}</td></tr>
                        <tr><td class="bg-light"><strong>Kelurahan/Desa</strong></td><td>{{ $siswa->kelurahanSiswa?->name ?? $dash }}</td></tr>
                        <tr><td class="bg-light"><strong>Kecamatan</strong></td><td>{{ $siswa->kecamatanSiswa?->name ?? $dash }}</td></tr>
                        <tr><td class="bg-light"><strong>Kab/Kota</strong></td><td>{{ $siswa->kabupatenSiswa?->name ?? $dash }}</td></tr>
                        <tr><td class="bg-light"><strong>Provinsi</strong></td><td>{{ $siswa->provinsiSiswa?->name ?? $dash }}</td></tr>
                        <tr><td class="bg-light"><strong>Kode Pos</strong></td><td>{{ $siswa->kodepos_siswa ?: $dash }}</td></tr>
                    </table></div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="wali-data-ortu" role="tabpanel">
            @if($siswa->ortu)
                <div class="row">
                    @foreach([['title' => 'Data Ayah', 'icon' => 'male', 'key' => 'ayah'], ['title' => 'Data Ibu', 'icon' => 'female', 'key' => 'ibu']] as $parent)
                        @php $key = $parent['key']; @endphp
                        <div class="col-lg-6">
                            <h6 class="text-primary font-weight-bold"><i class="fas fa-{{ $parent['icon'] }} mr-1"></i> {{ $parent['title'] }}</h6>
                            <div class="table-responsive"><table class="table table-sm table-bordered table-detail">
                                <tr><td class="bg-light" width="40%"><strong>Status</strong></td><td>{{ data_get($siswa->ortu, 'status_'.$key) ? \Illuminate\Support\Str::headline(data_get($siswa->ortu, 'status_'.$key)) : $dash }}</td></tr>
                                <tr><td class="bg-light"><strong>Nama</strong></td><td>{{ data_get($siswa->ortu, 'nama_'.$key) ?: $dash }}</td></tr>
                                <tr><td class="bg-light"><strong>NIK</strong></td><td>{{ data_get($siswa->ortu, 'nik_'.$key) ?: $dash }}</td></tr>
                                <tr><td class="bg-light"><strong>No. HP</strong></td><td>
                                    @php $phone = data_get($siswa->ortu, 'hp_'.$key); @endphp
                                    @if($phone)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" data-no-overlay><i class="fas fa-phone-alt mr-1"></i>{{ $phone }}</a>@else{{ $dash }}@endif
                                </td></tr>
                                <tr><td class="bg-light"><strong>Pekerjaan</strong></td><td>{{ data_get($siswa->ortu, 'pekerjaan_'.$key) ?: $dash }}</td></tr>
                                <tr><td class="bg-light"><strong>Penghasilan</strong></td><td>{{ data_get($siswa->ortu, 'penghasilan_'.$key) ?: $dash }}</td></tr>
                            </table></div>
                        </div>
                    @endforeach
                </div>
                <h6 class="text-primary font-weight-bold mt-3"><i class="fas fa-home mr-1"></i> Alamat Orang Tua</h6>
                <div class="table-responsive"><table class="table table-sm table-bordered table-detail">
                    <tr><td class="bg-light" width="20%"><strong>No. KK</strong></td><td>{{ $siswa->ortu->no_kk ?: $dash }}</td></tr>
                    <tr><td class="bg-light"><strong>Alamat Lengkap</strong></td><td>{{ $siswa->ortu->getAlamatLengkap() ?: $dash }}</td></tr>
                    <tr><td class="bg-light"><strong>RT / RW</strong></td><td>{{ $siswa->ortu->rt_ortu ?: $dash }} / {{ $siswa->ortu->rw_ortu ?: $dash }}</td></tr>
                    <tr><td class="bg-light"><strong>Kelurahan/Desa</strong></td><td>{{ $siswa->ortu->kelurahan?->name ?? $dash }}</td></tr>
                    <tr><td class="bg-light"><strong>Kecamatan</strong></td><td>{{ $siswa->ortu->kecamatan?->name ?? $dash }}</td></tr>
                    <tr><td class="bg-light"><strong>Kab/Kota</strong></td><td>{{ $siswa->ortu->kabupaten?->name ?? $dash }}</td></tr>
                    <tr><td class="bg-light"><strong>Provinsi</strong></td><td>{{ $siswa->ortu->provinsi?->name ?? $dash }}</td></tr>
                    <tr><td class="bg-light"><strong>Kode Pos</strong></td><td>{{ $siswa->ortu->kodepos ?: $dash }}</td></tr>
                </table></div>
            @else
                <div class="callout callout-warning mb-0">Data orang tua belum tersedia.</div>
            @endif
        </div>

        <div class="tab-pane fade" id="wali-sekolah-asal" role="tabpanel">
            @if($siswa->sekolahAsal)
                <div class="row">
                    <div class="col-lg-6"><h6 class="text-primary font-weight-bold"><i class="fas fa-school mr-1"></i> Informasi Sekolah</h6>
                        <div class="table-responsive"><table class="table table-sm table-bordered table-detail">
                            <tr><td class="bg-light" width="40%"><strong>NPSN</strong></td><td>{{ $siswa->sekolahAsal->npsn ?: $dash }}</td></tr>
                            <tr><td class="bg-light"><strong>NSM</strong></td><td>{{ $siswa->sekolahAsal->nsm ?: $dash }}</td></tr>
                            <tr><td class="bg-light"><strong>Nama Sekolah</strong></td><td>{{ $siswa->sekolahAsal->nama ?: $dash }}</td></tr>
                            <tr><td class="bg-light"><strong>Bentuk Pendidikan</strong></td><td>{{ $siswa->sekolahAsal->bentuk_pendidikan ?: $dash }}</td></tr>
                            <tr><td class="bg-light"><strong>Status</strong></td><td>{{ $siswa->sekolahAsal->status_sekolah ?: $dash }}</td></tr>
                        </table></div>
                    </div>
                    <div class="col-lg-6"><h6 class="text-primary font-weight-bold"><i class="fas fa-map-marker-alt mr-1"></i> Lokasi Sekolah</h6>
                        <div class="table-responsive"><table class="table table-sm table-bordered table-detail">
                            <tr><td class="bg-light" width="40%"><strong>Provinsi</strong></td><td>{{ $siswa->sekolahAsal->provinsi ?: $dash }}</td></tr>
                            <tr><td class="bg-light"><strong>Kab/Kota</strong></td><td>{{ $siswa->sekolahAsal->kabupaten_kota ?: $dash }}</td></tr>
                            <tr><td class="bg-light"><strong>Kecamatan</strong></td><td>{{ $siswa->sekolahAsal->kecamatan ?: $dash }}</td></tr>
                            <tr><td class="bg-light"><strong>Alamat</strong></td><td>{{ $siswa->sekolahAsal->alamat_jalan ?: $dash }}</td></tr>
                        </table></div>
                    </div>
                </div>
            @else
                <div class="callout callout-info mb-0">Data sekolah asal belum tersedia{{ $siswa->npsn_asal_sekolah ? ' untuk NPSN '.$siswa->npsn_asal_sekolah : '' }}.</div>
            @endif
        </div>

        <div class="tab-pane fade" id="wali-dokumen" role="tabpanel">
            @forelse($siswa->dokumen as $dokumen)
                <div class="border rounded p-3 mb-2 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center">
                    <div class="mb-2 mb-sm-0">
                        <div class="font-weight-bold"><i class="fas {{ $dokumen->mime_type === 'application/pdf' ? 'fa-file-pdf text-danger' : 'fa-file-image text-primary' }} mr-1"></i> {{ $dokumen->getJenisDokumenLabel() }}</div>
                        <small class="text-muted">{{ $dokumen->getFileSizeFormatted() }} · diunggah {{ $dokumen->created_at?->format('d/m/Y') ?? $dash }}</small>
                        @if($dokumen->keterangan)<div class="small mt-1">{{ $dokumen->keterangan }}</div>@endif
                    </div>
                    @can('view-siswa')
                        <a href="{{ $dokumen->getFileUrl() }}" target="_blank" rel="noopener" class="btn btn-sm btn-primary" data-no-overlay><i class="fas fa-eye mr-1"></i> Lihat</a>
                    @else
                        <span class="badge badge-light border">Dokumen tersedia</span>
                    @endcan
                </div>
            @empty
                <div class="callout callout-info mb-0">Belum ada dokumen yang diunggah.</div>
            @endforelse
        </div>

        <div class="tab-pane fade" id="wali-catatan" role="tabpanel">
            @forelse($catatan as $c)
                <div class="border rounded p-3 mb-2 {{ $c->is_penting ? 'border-warning' : '' }}">
                    <div class="d-flex flex-wrap justify-content-between">
                        <span class="text-muted small">{{ $c->tanggal->translatedFormat('d F Y') }}</span>
                        <div>@if($c->kategori)<span class="badge badge-info">{{ $c->kategori_label }}</span>@endif @if($c->is_penting)<span class="badge badge-warning">Penting</span>@endif</div>
                    </div>
                    <div class="mt-2">{{ $c->catatan }}</div>
                </div>
            @empty
                <div class="callout callout-info mb-0">Belum ada catatan wali kelas.</div>
            @endforelse
        </div>
    </div>
</div>
