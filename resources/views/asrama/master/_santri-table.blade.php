<div class="table-responsive">
    <table class="table table-hover table-sm">
        <thead class="thead-light"><tr>
            <th class="text-center" style="width:56px">Foto</th>
            <th>Nama / NISN</th>
            <th class="text-center">JK</th>
            <th>Kelas</th>
            <th>Pengasuh</th>
            <th>Kamar</th>
            <th class="text-center">Status</th>
            <th class="text-center">Tgl Masuk</th>
            <th class="text-center" style="width:110px">Aksi</th>
        </tr></thead>
        <tbody>
@forelse($records as $item)
@php
    $siswa = $item->siswa;
    $fallback = 'https://ui-avatars.com/api/?name='.urlencode($siswa->nama_lengkap ?? 'Santri').'&size=100&background='.($siswa->jenis_kelamin === 'L' ? '3498db' : 'e83e8c').'&color=FFFFFF&font-size=0.45&bold=true';
    $fotoUrl = $siswa->foto_profile ? $siswa->foto_profile_url : $fallback;
@endphp
<tr>
    <td class="text-center align-middle">
        @if($siswa->foto_profile)
        <button type="button" class="btn btn-link p-0 border-0 js-preview-foto" data-preview-url="{{ $fotoUrl }}" data-student-name="{{ $siswa->nama_lengkap }}" title="Klik untuk preview foto">
            <img src="{{ $fotoUrl }}" alt="Foto {{ $siswa->nama_lengkap }}" class="img-circle shadow-sm" onerror="this.onerror=null;this.src='{{ $fallback }}';" style="width:36px;height:36px;object-fit:cover;">
        </button>
        @else
        <img src="{{ $fallback }}" alt="{{ $siswa->nama_lengkap }}" class="img-circle" style="width:36px;height:36px;object-fit:cover;opacity:.7;">
        @endif
    </td>
    <td class="align-middle">
        <strong>{{ $siswa->nama_lengkap }}</strong><br>
        <small class="text-muted">NISN {{ $siswa->nisn ?: '-' }} &middot; No. Induk <code>{{ $item->nomor_induk_asrama ?: '-' }}</code></small>
    </td>
    <td class="text-center align-middle">{!! $siswa->jenis_kelamin === 'L' ? '<span class="badge badge-primary">L</span>' : '<span class="badge badge-danger">P</span>' !!}</td>
    <td class="align-middle">{{ $item->kelasAktif?->kelas?->kelasReguler?->nama_kelas ?? $siswa->kelasTahunAktif->first()?->nama_kelas ?? '-' }}</td>
    <td class="align-middle">{{ $item->kelasAktif?->pengasuhAssignment?->rombelPengasuh?->pengasuh?->gtk?->nama_lengkap ?? 'Belum dibagi' }}</td>
    <td class="align-middle">{{ $item->kamarAktif?->kamar?->nama ?? 'Belum ditempatkan' }}</td>
    <td class="text-center align-middle"><span class="badge {{ $item->status==='aktif'?'badge-success':'badge-secondary' }}">{{ ucfirst($item->status) }}</span></td>
    <td class="text-center align-middle"><small>{{ $item->tanggal_masuk?->format('d/m/Y') ?: '-' }}</small></td>
    <td class="text-center align-middle text-nowrap">
        <button type="button" class="btn btn-sm btn-info" onclick="showSantri('{{ $item->id }}')" title="Detail santri"><i class="fas fa-eye"></i></button>
        <form method="post" action="{{ route('asrama.santri.destroy', $item) }}" class="d-inline" data-asrama-loading data-confirm="Hapus {{ $siswa->nama_lengkap }} dari asrama? Data siswa SIMANSA tidak akan terhapus.">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-danger" title="Hapus dari asrama"><i class="fas fa-trash"></i></button></form>
    </td>
</tr>
@empty<tr><td colspan="9" class="text-center text-muted py-5"><i class="fas fa-user-graduate d-block mb-2" style="font-size:2rem"></i>Tidak ada santri yang cocok dengan filter.</td></tr>@endforelse
        </tbody>
    </table>
</div>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mt-2">
    <small class="text-muted mb-2 mb-md-0">Menampilkan {{ $records->firstItem() ?: 0 }}&ndash;{{ $records->lastItem() ?: 0 }} dari {{ number_format($records->total()) }} santri</small>
    {{ $records->links() }}
</div>
