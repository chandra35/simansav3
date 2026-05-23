@extends('adminlte::page')

@section('title', 'Notifikasi Exam Browser')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-bell"></i> Notifikasi Exam Browser</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('admin.exam-browser.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Exam Browser
                </a>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-times-circle"></i> {{ session('error') }}
            </div>
        @endif
    </div>

    <div class="col-12">
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $notifications->total() }}</h3>
                        <p>Total Riwayat Notifikasi</p>
                    </div>
                    <div class="icon"><i class="fas fa-history"></i></div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $notifications->where('is_active', true)->count() }}</h3>
                        <p>Notifikasi Aktif</p>
                    </div>
                    <div class="icon"><i class="fas fa-bolt"></i></div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="small-box {{ $fcmConfigured ? 'bg-primary' : 'bg-warning' }}">
                    <div class="inner">
                        <h3>{{ $fcmConfigured ? 'ON' : 'OFF' }}</h3>
                        <p>Push Realtime FCM</p>
                    </div>
                    <div class="icon"><i class="fas fa-satellite-dish"></i></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Form Kirim Notifikasi --}}
    <div class="col-md-5">
        <div class="card simansa-management-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-paper-plane"></i> Kirim Overlay Baru</h3>
            </div>
            <form action="{{ route('admin.exam-notifications.store') }}" method="POST">
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
                        <small class="text-muted">Contoh token: <strong>TOKEN UJIAN: 382914</strong> atau info penting lain yang harus langsung muncul di APK.</small>
                    </div>

                    <div class="form-group">
                        <label for="display_seconds">Durasi Overlay (detik) <span class="text-danger">*</span></label>
                        <input type="number" name="display_seconds" id="display_seconds" class="form-control"
                               min="3" max="60" value="{{ old('display_seconds', 10) }}" required>
                        <small class="text-muted">Saat APK terbuka, pesan akan muncul sebagai overlay selama durasi ini.</small>
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
                        <i class="fas fa-paper-plane"></i> Kirim Overlay ke Aplikasi
                    </button>
                </div>
            </form>
        </div>

        {{-- Info Card --}}
        <div class="card card-{{ $fcmConfigured ? 'success' : 'warning' }} card-outline">
            <div class="card-body">
                @if($fcmConfigured)
                    <h6><i class="fas fa-bolt text-success"></i> Push Notification Aktif (Realtime)</h6>
                    <ul class="mb-0 pl-3">
                        <li><strong>Saat app terbuka:</strong> Notifikasi muncul <strong>INSTAN</strong> via push</li>
                        <li><strong>Saat app tertutup:</strong> Notifikasi muncul <strong>INSTAN</strong> via push</li>
                        <li>Tipe <strong>Urgent</strong> akan muncul sebagai dialog popup di app</li>
                        <li>Tipe <strong>Info/Warning</strong> muncul sebagai notifikasi biasa</li>
                    </ul>
                @else
                    <h6><i class="fas fa-exclamation-triangle text-warning"></i> Push Notification Belum Dikonfigurasi</h6>
                    <ul class="mb-0 pl-3">
                        <li><strong>Saat app terbuka:</strong> Notifikasi tidak akan terkirim sebelum FCM aktif</li>
                        <li><strong>Saat app tertutup:</strong> Notifikasi tidak akan terkirim sebelum FCM aktif</li>
                        <li class="text-info mt-1">Konfigurasi Firebase untuk push notification realtime</li>
                        <li class="text-muted"><small>Letakkan satu file service account Firebase <code>.json</code> di <code>storage/app/firebase/</code> atau set <code>FIREBASE_CREDENTIALS</code> ke path yang benar</small></li>
                    </ul>
                @endif
            </div>
        </div>
    </div>

    {{-- Daftar Notifikasi --}}
    <div class="col-md-7">
        <div class="card simansa-management-card">
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
                                    <th style="width: 90px">Overlay</th>
                                    <th style="width: 100px">Target</th>
                                    <th style="width: 140px">Waktu</th>
                                    <th style="width: 140px">Aksi</th>
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
                                        <span class="badge badge-dark">{{ $notif->display_seconds ?? 10 }} dtk</span>
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
                                        <div class="btn-group btn-group-sm">
                                            <form action="{{ route('admin.exam-notifications.resend', $notif) }}" method="POST"
                                                  onsubmit="return confirm('Kirim ulang notifikasi ini ke aplikasi?')">
                                                @csrf
                                                <button type="submit" class="btn btn-info" title="Kirim Ulang">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            </form>

                                            @if($notif->is_active)
                                                <form action="{{ route('admin.exam-notifications.destroy', $notif) }}" method="POST"
                                                      onsubmit="return confirm('Nonaktifkan notifikasi ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-warning" title="Nonaktifkan">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            <form action="{{ route('admin.exam-notifications.force-delete', $notif->id) }}" method="POST"
                                                  onsubmit="return confirm('Hapus permanen notifikasi ini dari riwayat?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" title="Hapus Permanen">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
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
