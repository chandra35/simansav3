@extends('adminlte::page')

@section('title', 'Pengaturan Download Center')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-cog"></i> Pengaturan Download Center</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('admin.downloads.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
@endsection

@section('content')
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="card simansa-management-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-hdd"></i> Storage & Google Drive</h3>
        </div>
        <div class="card-body">
            <form id="downloadSettingsForm" method="POST" action="{{ route('admin.download-settings.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Default Storage</label>
                    <select name="default_storage" class="form-control">
                        <option value="local" {{ old('default_storage', $setting->default_storage) === 'local' ? 'selected' : '' }}>Local Simansa</option>
                        <option value="gdrive" {{ old('default_storage', $setting->default_storage) === 'gdrive' ? 'selected' : '' }}>Google Drive</option>
                    </select>
                </div>

                <hr>
                <h5><i class="fab fa-google-drive"></i> Google Drive</h5>

                <div class="form-group">
                    <label>Mode Auth</label>
                    <select name="gdrive_auth_mode" class="form-control">
                        <option value="service_account" {{ old('gdrive_auth_mode', $setting->gdrive_auth_mode) === 'service_account' ? 'selected' : '' }}>Service Account JSON</option>
                        <option value="oauth" {{ old('gdrive_auth_mode', $setting->gdrive_auth_mode) === 'oauth' ? 'selected' : '' }}>OAuth (refresh token)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Root Folder ID</label>
                    <input type="text" class="form-control" name="gdrive_root_folder_id" value="{{ old('gdrive_root_folder_id', $setting->gdrive_root_folder_id) }}" placeholder="Contoh: 1AbCdEf...">
                </div>

                <div class="form-group">
                    <label>Upload JSON Service Account</label>
                    <input type="file" class="form-control-file" name="gdrive_credentials_file" accept=".json,application/json,text/plain">
                    @if($setting->gdrive_credentials_path)
                        <small class="text-success d-block mt-1">Credential tersimpan: {{ basename($setting->gdrive_credentials_path) }}</small>
                    @endif
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>OAuth Client ID</label>
                            <input type="text" class="form-control" name="gdrive_oauth_client_id" value="{{ old('gdrive_oauth_client_id', $setting->gdrive_oauth_client_id) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>OAuth Email</label>
                            <input type="email" class="form-control" name="gdrive_oauth_email" value="{{ old('gdrive_oauth_email', $setting->gdrive_oauth_email) }}">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>OAuth Client Secret</label>
                    <input type="password" class="form-control" name="gdrive_oauth_client_secret" placeholder="Isi jika ingin ganti">
                </div>

                <div class="form-group">
                    <label>OAuth Refresh Token</label>
                    <textarea class="form-control" rows="2" name="gdrive_oauth_refresh_token" placeholder="Isi jika ingin ganti"></textarea>
                </div>

                <div class="custom-control custom-switch mb-3">
                    <input type="checkbox" class="custom-control-input" id="gdrive_make_public" name="gdrive_make_public" value="1" {{ old('gdrive_make_public', $setting->gdrive_make_public) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="gdrive_make_public">Buat file publik (recommended)</label>
                </div>

                <div class="d-flex">
                    <button class="btn btn-primary mr-2" type="submit"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </form>

            <form id="testConnectionForm" method="POST" action="{{ route('admin.download-settings.test-connection') }}" class="mt-2">
                @csrf
                <button class="btn btn-info" type="submit"><i class="fas fa-plug"></i> Test Koneksi GDrive</button>
            </form>
        </div>
    </div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function () {
    $('#downloadSettingsForm').on('submit', function (event) {
        if ($(this).data('confirmed')) {
            return;
        }

        event.preventDefault();

        Swal.fire({
            title: 'Simpan Pengaturan Storage?',
            text: 'Perubahan pengaturan akan langsung diterapkan untuk upload berikutnya.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $(this).data('confirmed', true);
                this.submit();
            }
        });
    });

    $('#testConnectionForm').on('submit', function (event) {
        if ($(this).data('confirmed')) {
            return;
        }

        event.preventDefault();

        Swal.fire({
            title: 'Test Koneksi Google Drive?',
            text: 'Sistem akan mencoba autentikasi dan cek akses folder Google Drive.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Test Sekarang',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $(this).data('confirmed', true);
                this.submit();
            }
        });
    });
});
</script>
@endsection
