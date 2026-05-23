@extends('adminlte::page')

@section('title', 'Notifikasi Exam Browser')

@section('css')
<style>
    .bulk-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .9rem 1rem;
        background: linear-gradient(135deg, rgba(13, 110, 253, .08), rgba(23, 162, 184, .12));
        border-bottom: 1px solid rgba(0, 123, 255, .12);
    }

    .bulk-toolbar__meta {
        display: flex;
        align-items: center;
        gap: .75rem;
        color: #495057;
        font-size: .92rem;
        font-weight: 600;
    }

    .bulk-toolbar__actions {
        display: flex;
        align-items: center;
        gap: .5rem;
        flex-wrap: wrap;
    }

    .notification-row.is-selected {
        background-color: rgba(13, 110, 253, .06) !important;
    }

    .notification-check {
        transform: scale(1.15);
    }

    .confirm-modal .modal-content {
        border: 0;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .25);
    }

    .confirm-modal__top {
        padding: 1.4rem 1.5rem 1rem;
        background: radial-gradient(circle at top left, rgba(255,255,255,.22), transparent 55%), linear-gradient(135deg, #0f4c81, #0d6efd 58%, #17a2b8);
        color: #fff;
    }

    .confirm-modal__icon {
        width: 58px;
        height: 58px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,.16);
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .confirm-modal__body {
        padding: 1.35rem 1.5rem 1.5rem;
    }

    .confirm-modal__summary {
        margin-top: 1rem;
        padding: .9rem 1rem;
        border-radius: 14px;
        background: #f8fafc;
        color: #495057;
        font-size: .92rem;
    }

    .confirm-modal__actions {
        display: flex;
        justify-content: flex-end;
        gap: .65rem;
        padding: 0 1.5rem 1.5rem;
    }

    @media (max-width: 767.98px) {
        .bulk-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .bulk-toolbar__actions {
            width: 100%;
        }

        .bulk-toolbar__actions .btn,
        .bulk-toolbar__actions .input-group {
            width: 100%;
        }
    }
</style>
@endsection

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
                    <form id="bulkActionForm" action="{{ route('admin.exam-notifications.bulk-action') }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" id="bulkActionInput">
                        <div class="bulk-toolbar">
                            <div class="bulk-toolbar__meta">
                                <div class="custom-control custom-checkbox mb-0">
                                    <input type="checkbox" class="custom-control-input" id="selectAllNotifications">
                                    <label class="custom-control-label" for="selectAllNotifications">Pilih semua pada halaman ini</label>
                                </div>
                                <span><span id="selectedNotificationCount">0</span> notifikasi dipilih</span>
                            </div>
                            <div class="bulk-toolbar__actions">
                                <button type="button" class="btn btn-sm btn-info" data-bulk-action="resend">
                                    <i class="fas fa-paper-plane"></i> Kirim Ulang Terpilih
                                </button>
                                <button type="button" class="btn btn-sm btn-warning" data-bulk-action="deactivate">
                                    <i class="fas fa-ban"></i> Nonaktifkan Terpilih
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" data-bulk-action="force_delete">
                                    <i class="fas fa-trash"></i> Hapus Permanen Terpilih
                                </button>
                            </div>
                        </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 44px" class="text-center">
                                        <i class="fas fa-check-square text-muted"></i>
                                    </th>
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
                                <tr class="notification-row {{ !$notif->is_active ? 'text-muted' : '' }}" data-row-id="{{ $notif->id }}">
                                    <td class="text-center align-middle">
                                        <input type="checkbox"
                                               class="notification-check"
                                               name="notification_ids[]"
                                               value="{{ $notif->id }}"
                                               aria-label="Pilih notifikasi {{ $notif->title }}">
                                    </td>
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
                                                  class="js-confirmable-form"
                                                  data-confirm-title="Kirim ulang notifikasi?"
                                                  data-confirm-message="Notifikasi ini akan diduplikasi lalu dikirim ulang ke aplikasi siswa."
                                                  data-confirm-icon="paper-plane"
                                                  data-confirm-button="Kirim Ulang"
                                                  data-confirm-button-class="btn-info">
                                                @csrf
                                                <button type="submit" class="btn btn-info" title="Kirim Ulang">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            </form>

                                            @if($notif->is_active)
                                                <form action="{{ route('admin.exam-notifications.destroy', $notif) }}" method="POST"
                                                                                                            class="js-confirmable-form"
                                                                                                            data-confirm-title="Nonaktifkan notifikasi?"
                                                                                                            data-confirm-message="Notifikasi ini tidak akan dianggap aktif lagi di riwayat."
                                                                                                            data-confirm-icon="ban"
                                                                                                            data-confirm-button="Nonaktifkan"
                                                                                                            data-confirm-button-class="btn-warning">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-warning" title="Nonaktifkan">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            <form action="{{ route('admin.exam-notifications.force-delete', $notif->id) }}" method="POST"
                                                  class="js-confirmable-form"
                                                  data-confirm-title="Hapus permanen notifikasi?"
                                                  data-confirm-message="Data riwayat ini akan dihapus permanen dan tidak bisa dikembalikan."
                                                  data-confirm-icon="trash"
                                                  data-confirm-button="Hapus Permanen"
                                                  data-confirm-button-class="btn-danger">
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
                    </form>
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
<div class="modal fade confirm-modal" id="notificationConfirmModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="confirm-modal__top">
                <div class="confirm-modal__icon">
                    <i class="fas fa-paper-plane" id="notificationConfirmIcon"></i>
                </div>
                <h4 class="mb-1" id="notificationConfirmTitle">Konfirmasi Aksi</h4>
                <p class="mb-0 text-white-50" id="notificationConfirmSubtitle">Periksa aksi sebelum dilanjutkan.</p>
            </div>
            <div class="confirm-modal__body">
                <p class="mb-0" id="notificationConfirmMessage">Aksi ini akan memproses data yang dipilih.</p>
                <div class="confirm-modal__summary" id="notificationConfirmSummary"></div>
            </div>
            <div class="confirm-modal__actions">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="notificationConfirmSubmit">Lanjutkan</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $(function () {
        const $bulkForm = $('#bulkActionForm');
        const $selectAll = $('#selectAllNotifications');
        const $checks = $('.notification-check');
        const $selectedCount = $('#selectedNotificationCount');
        const $modal = $('#notificationConfirmModal');
        const $modalTitle = $('#notificationConfirmTitle');
        const $modalSubtitle = $('#notificationConfirmSubtitle');
        const $modalMessage = $('#notificationConfirmMessage');
        const $modalSummary = $('#notificationConfirmSummary');
        const $modalIcon = $('#notificationConfirmIcon');
        const $modalSubmit = $('#notificationConfirmSubmit');
        const $bulkActionInput = $('#bulkActionInput');

        let pendingSubmit = null;

        function updateSelectionState() {
            const checked = $checks.filter(':checked');
            $selectedCount.text(checked.length);
            $selectAll.prop('checked', checked.length > 0 && checked.length === $checks.length);

            $checks.each(function () {
                const rowId = $(this).val();
                const $row = $('.notification-row[data-row-id="' + rowId + '"]');
                $row.toggleClass('is-selected', $(this).is(':checked'));
            });
        }

        function openConfirmModal(options) {
            pendingSubmit = options.onConfirm;
            $modalTitle.text(options.title);
            $modalSubtitle.text(options.subtitle || 'Periksa aksi sebelum dilanjutkan.');
            $modalMessage.text(options.message);
            $modalSummary.html(options.summary || '');
            $modalIcon.attr('class', 'fas fa-' + (options.icon || 'paper-plane'));
            $modalSubmit.attr('class', 'btn ' + (options.buttonClass || 'btn-primary')).text(options.buttonText || 'Lanjutkan');
            $modal.modal('show');
        }

        $checks.on('change', updateSelectionState);
        $selectAll.on('change', function () {
            $checks.prop('checked', $(this).is(':checked'));
            updateSelectionState();
        });

        $('[data-bulk-action]').on('click', function () {
            const action = $(this).data('bulk-action');
            const selected = $checks.filter(':checked');

            if (!selected.length) {
                openConfirmModal({
                    title: 'Belum ada notifikasi dipilih',
                    subtitle: 'Pilih minimal satu baris dari riwayat notifikasi.',
                    message: 'Bulk action membutuhkan minimal satu notifikasi yang dicentang terlebih dahulu.',
                    summary: 'Tips: gunakan checkbox di kiri tabel atau tombol pilih semua pada halaman ini.',
                    icon: 'check-square',
                    buttonText: 'Tutup',
                    buttonClass: 'btn-secondary',
                    onConfirm: function () {
                        $modal.modal('hide');
                    }
                });
                return;
            }

            const actionMap = {
                resend: {
                    title: 'Kirim ulang notifikasi terpilih?',
                    message: 'Setiap notifikasi akan diduplikasi lalu dikirim kembali ke aplikasi siswa.',
                    summary: '<strong>' + selected.length + ' notifikasi</strong> akan dibuat ulang sebagai riwayat baru dan dicoba kirim realtime.',
                    icon: 'paper-plane',
                    buttonText: 'Kirim Ulang Sekarang',
                    buttonClass: 'btn-info'
                },
                deactivate: {
                    title: 'Nonaktifkan notifikasi terpilih?',
                    message: 'Notifikasi yang masih aktif akan ditandai nonaktif di riwayat.',
                    summary: '<strong>' + selected.length + ' notifikasi</strong> akan dinonaktifkan pada halaman ini.',
                    icon: 'ban',
                    buttonText: 'Nonaktifkan',
                    buttonClass: 'btn-warning'
                },
                force_delete: {
                    title: 'Hapus permanen notifikasi terpilih?',
                    message: 'Aksi ini akan menghapus riwayat notifikasi yang dipilih secara permanen.',
                    summary: '<strong>' + selected.length + ' notifikasi</strong> akan dihapus permanen dan tidak bisa dikembalikan.',
                    icon: 'trash',
                    buttonText: 'Hapus Permanen',
                    buttonClass: 'btn-danger'
                }
            };

            const config = actionMap[action];
            openConfirmModal({
                title: config.title,
                message: config.message,
                summary: config.summary,
                icon: config.icon,
                buttonText: config.buttonText,
                buttonClass: config.buttonClass,
                onConfirm: function () {
                    $bulkActionInput.val(action);
                    $bulkForm.trigger('submit');
                }
            });
        });

        $('.js-confirmable-form').on('submit', function (event) {
            event.preventDefault();
            const form = this;
            const $form = $(form);
            const title = $form.data('confirm-title') || 'Konfirmasi Aksi';
            const message = $form.data('confirm-message') || 'Aksi ini akan diproses sekarang.';
            const icon = $form.data('confirm-icon') || 'paper-plane';
            const buttonText = $form.data('confirm-button') || 'Lanjutkan';
            const buttonClass = $form.data('confirm-button-class') || 'btn-primary';

            openConfirmModal({
                title: title,
                message: message,
                summary: 'Target aksi: <strong>' + ($form.closest('tr').find('strong').first().text() || 'Notifikasi terpilih') + '</strong>.',
                icon: icon,
                buttonText: buttonText,
                buttonClass: buttonClass,
                onConfirm: function () {
                    form.submit();
                }
            });
        });

        $modalSubmit.on('click', function () {
            if (typeof pendingSubmit === 'function') {
                const currentAction = pendingSubmit;
                pendingSubmit = null;
                $modal.modal('hide');
                currentAction();
            } else {
                $modal.modal('hide');
            }
        });

        $modal.on('hidden.bs.modal', function () {
            pendingSubmit = null;
        });

        updateSelectionState();
    });
</script>
@endsection
