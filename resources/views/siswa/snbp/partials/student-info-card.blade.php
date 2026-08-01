<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-user"></i> Data Siswa
        </h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="35%"><strong>Nama</strong></td>
                        <td>: {{ $siswa->nama_lengkap }}</td>
                    </tr>
                    <tr>
                        <td><strong>NISN</strong></td>
                        <td>: {{ $siswa->nisn }}</td>
                    </tr>
                    <tr>
                        <td><strong>NIS</strong></td>
                        <td>: {{ $siswa->nis ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tanggal Lahir</strong></td>
                        <td>: {{ $siswa->tanggal_lahir ? $siswa->tanggal_lahir->translatedFormat('j F Y') : '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="35%"><strong>Kelas</strong></td>
                        <td>: {{ $siswa->kelasSaatIni->nama_kelas ?? '-' }}{{ $siswa->kelasSaatIni->asrama_suffix ?? '' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Jurusan</strong></td>
                        <td>: {{ $siswa->kelasSaatIni->jurusan->nama ?? ($siswa->kelasSaatIni->jurusan->singkatan ?? '-') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tahun Pelajaran</strong></td>
                        <td>: {{ $snbpMenu->tahunPelajaran->nama ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
