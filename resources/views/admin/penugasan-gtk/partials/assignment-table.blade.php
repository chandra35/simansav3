<div class="table-responsive">
    <table class="table table-hover table-bordered">
        <thead><tr><th>GTK</th><th>Penugasan</th><th>Periode Akademik</th><th class="text-center">JTM</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
        <tbody>
        @forelse($assignments as $assignment)
            @php
                $badge = ['active' => 'success', 'ended' => 'secondary', 'draft' => 'warning', 'cancelled' => 'danger'][$assignment->status] ?? 'secondary';
                $statusLabel = ['active' => 'Aktif', 'ended' => 'Lepas / selesai', 'draft' => 'Draft', 'cancelled' => 'Dibatalkan'][$assignment->status] ?? ucfirst($assignment->status);
                $assignmentRecord = ['gtk_id' => $assignment->gtk_id, 'jenis_penugasan_id' => $assignment->jenis_penugasan_id, 'tahun_pelajaran_id' => $assignment->tahun_pelajaran_id, 'semester' => $assignment->semester, 'unit_nama' => $assignment->unit_nama, 'keterangan' => $assignment->keterangan];
            @endphp
            <tr>
                <td><div class="d-flex align-items-center"><img src="{{ $assignment->gtk->foto_profile_url }}" class="assignment-photo mr-2" alt=""><div><strong>{{ $assignment->gtk->nama_lengkap }}</strong><small class="d-block text-muted">NIP {{ $assignment->gtk->nip ?: '-' }}</small></div></div></td>
                <td><strong>{{ $assignment->jenis->nama }}</strong>@if($assignment->unit_nama)<small class="d-block text-muted"><i class="fas fa-map-marker-alt mr-1"></i>{{ $assignment->unit_nama }}</small>@endif<small class="d-block text-muted">{{ $assignment->jenis->dasar_hukum ?: 'Standar internal' }}</small></td>
                <td><strong>{{ $assignment->tahunPelajaran?->nama ?: '-' }}</strong><small class="d-block text-muted">{{ $assignment->semester ? 'Semester '.$assignment->semester : 'Sepanjang tahun' }}</small></td>
                <td class="text-center"><span class="badge badge-primary p-2">{{ $assignment->ekuivalensi_jtm }} JTM</span></td>
                <td><span class="badge badge-{{ $badge }}">{{ $statusLabel }}</span>@if($assignment->selesai_tugas && $assignment->status === 'ended')<small class="d-block text-muted">Sejak {{ $assignment->selesai_tugas->translatedFormat('d M Y') }}</small>@endif @if($assignment->legacy_tugas_tambahan_id)<small class="d-block text-muted">Migrasi data lama</small>@endif</td>
                <td class="text-right text-nowrap">
                    @if($assignment->status === 'active')
                        @can('edit-penugasan-gtk')<button class="btn btn-sm btn-primary edit-assignment" data-toggle="modal" data-target="#assignmentModal" data-url="{{ route('admin.penugasan-gtk.update', $assignment) }}" data-record='@json($assignmentRecord)' title="Edit"><i class="fas fa-edit"></i></button>@endcan
                        @can('end-penugasan-gtk')<button class="btn btn-sm btn-warning end-assignment" data-toggle="modal" data-target="#endAssignmentModal" data-url="{{ route('admin.penugasan-gtk.end', $assignment) }}" data-name="{{ $assignment->jenis->nama }} · {{ $assignment->gtk->nama_lengkap }}" title="Akhiri"><i class="fas fa-stop-circle"></i></button>@endcan
                    @elseif(! $assignment->legacy_tugas_tambahan_id)
                        @can('delete-penugasan-gtk')<form action="{{ route('admin.penugasan-gtk.destroy', $assignment) }}" method="POST" class="d-inline archive-form">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" title="Arsipkan"><i class="fas fa-archive"></i></button></form>@endcan
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2"></i>{{ $statusFilter === 'active' ? 'Belum ada penugasan aktif pada filter ini.' : 'Belum ada histori penugasan pada filter ini.' }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $assignments->links() }}
