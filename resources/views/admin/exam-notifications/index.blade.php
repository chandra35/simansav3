@extends('adminlte::page')

@section('title', 'Notifikasi Exam Browser')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-bell text-primary"></i> Notifikasi Exam Browser</h1>
    </div>
@stop

@section('content')
<div class="row">
    {{-- Form Kirim Notifikasi --}}
    <div class="col-md-5">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-paper-plane"></i> Kirim Notifikasi Baru</h3>
            </div>
            <form action="{{ route('exam-notifications.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="title">Judul <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title" class="form-control" 
                               placeholder="Contoh: Ujian dimulai 10 menit lagi" value="{{ old('title') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="message">Pesan <span class="text-danger">*</span></label>
                        <textarea name="message" id="message" class="form-control" rows="4"
                                  placeholder="Isi pesan notifikasi..." required>{{ old('message') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="type">Tipe</label>
                                <select name="type" id="type" class="form-control">
                                    <option value="info" {{ old('type') == 'info' ? 'selected' : '' }}>ℹ️ Info</option>
                                    <option value="warning" {{ old('type') == 'warning' ? 'selected' : '' }}>⚠️ Peringatan</option>
                                    <option value="urgent" {{ old('type') == 'urgent' ? 'selected' : '' }}>🚨 Urgent</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="target">Target</label>
                                <select name="target" id="target" class="form-control">
                                    <option value="all" {{ old('target') == 'all' ? 'selected' : '' }}>Semua Device</option>
                                    <option value="exam_active" {{ old('target') == 'exam_active' ? 'selected' : '' }}>Sedang Ujian</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="expires_at">Kedaluwarsa <small class="text-muted">(opsional)</small></label>
                        <input type="datetime-local" name="expires_at" id="expires_at" class="form-control"
                               value="{{ old('expires_at') }}">
                        <small class="text-muted">Kosongkan jika notifikasi tidak kedaluwarsa</small>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-paper-plane"></i> Kirim Notifikasi
                    </button>
                </div>
            </form>
        </div>

        {{-- Info Card --}}
        <div class="card card-info card-outline">
            <div class="card-body">
                <h6><i class="fas fa-info-circle"></i> Cara Kerja</h6>
                <ul class="mb-0 pl-3">
                    <li><strong>Saat app terbuka:</strong> Notifikasi muncul dalam <strong>~30 detik</strong></li>
                    <li><strong>Saat app tertutup:</strong> Notifikasi muncul dalam <strong>~15 menit</strong></li>
                    <li>Tipe <strong>Urgent</strong> akan muncul sebagai dialog popup di app</li>
                    <li>Tipe <strong>Info/Warning</strong> muncul sebagai notifikasi biasa</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Daftar Notifikasi --}}
    <div class="col-md-7">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history"></i> Riwayat Notifikasi</h3>
                <div class="card-tools">
                    <span class="badge badge-info">{{ $notifications->total() }} total</span>
                </div>
            </div>
            <div class="card-body p-0">
                @if($notifications->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-bell-slash fa-3x mb-3"></i>
                        <p>Belum ada notifikasi yang dikirim</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 50px">Tipe</th>
                                    <th>Judul & Pesan</th>
                                    <th style="width: 100px">Target</th>
                                    <th style="width: 140px">Waktu</th>
                                    <th style="width: 60px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($notifications as $notif)
                                <tr class="{{ !$notif->is_active ? 'text-muted' : '' }}">
                                    <td class="text-center">
                                        @if($notif->type === 'info')
                                            <span class="badge badge-info"><i class="fas fa-info-circle"></i></span>
                                        @elseif($notif->type === 'warning')
                                            <span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i></span>
                                        @else
                                            <span class="badge badge-danger"><i class="fas fa-exclamation-circle"></i></span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $notif->title }}</strong>
                                        @if(!$notif->is_active)
                                            <span class="badge badge-secondary">Nonaktif</span>
                                        @elseif($notif->expires_at && $notif->expires_at->isPast())
                                            <span class="badge badge-secondary">Expired</span>
                                        @endif
                                        <br>
                                        <small class="text-muted">{{ Str::limit($notif->message, 80) }}</small>
                                    </td>
                                    <td>
                                        @if($notif->target === 'all')
                                            <span class="badge badge-primary">Semua</span>
                                        @else
                                            <span class="badge badge-success">Ujian Aktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $notif->created_at->format('d/m/Y H:i') }}</small>
                                        @if($notif->sender)
                                            <br><small class="text-muted">oleh {{ $notif->sender->name }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($notif->is_active)
                                            <form action="{{ route('exam-notifications.destroy', $notif) }}" method="POST"
                                                  onsubmit="return confirm('Nonaktifkan notifikasi ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-danger" title="Nonaktifkan">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            @if($notifications->hasPages())
                <div class="card-footer">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@stop
