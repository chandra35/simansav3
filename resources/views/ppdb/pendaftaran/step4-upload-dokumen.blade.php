@extends('ppdb.layouts.app')

@section('title', 'Step 4 - Upload Dokumen')

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="container mx-auto px-4">
        <!-- Progress Steps -->
        <div class="max-w-4xl mx-auto mb-8">
            <div class="flex items-center justify-between">
                @php
                    $steps = [
                        ['num' => 1, 'label' => 'NISN'],
                        ['num' => 2, 'label' => 'Data Pribadi'],
                        ['num' => 3, 'label' => 'Data Orang Tua'],
                        ['num' => 4, 'label' => 'Upload Dokumen'],
                        ['num' => 5, 'label' => 'Review'],
                    ];
                    $currentStep = 4;
                @endphp
                
                @foreach($steps as $index => $step)
                    <div class="flex items-center {{ $index < count($steps) - 1 ? 'flex-1' : '' }}">
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm
                                {{ $step['num'] == $currentStep ? 'bg-blue-500 text-white' : ($step['num'] < $currentStep ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600') }}">
                                @if($step['num'] < $currentStep)
                                    <i class="fas fa-check"></i>
                                @else
                                    {{ $step['num'] }}
                                @endif
                            </div>
                            <span class="text-xs mt-2 text-gray-600 hidden sm:block">{{ $step['label'] }}</span>
                        </div>
                        @if($index < count($steps) - 1)
                            <div class="flex-1 h-1 mx-2 {{ $step['num'] < $currentStep ? 'bg-green-500' : 'bg-gray-300' }}"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Form Card -->
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-8 py-6">
                    <h1 class="text-2xl font-bold text-white">Upload Dokumen Persyaratan</h1>
                    <p class="text-white opacity-90 mt-1">No. Pendaftaran: <strong>{{ $pendaftaran->nomor_pendaftaran }}</strong></p>
                </div>

                <form action="{{ route('ppdb.pendaftaran.process-step4') }}" method="POST" class="p-8" id="formDokumen">
                    @csrf

                    @if($errors->any())
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                <strong>Terjadi kesalahan:</strong>
                            </div>
                            <ul class="list-disc list-inside text-sm">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                            <i class="fas fa-check-circle mr-2"></i>
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Info Box -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
                            <div class="text-sm text-blue-700">
                                <p class="font-semibold mb-1">Ketentuan Upload:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Format file: JPG, JPEG, PNG, atau PDF</li>
                                    <li>Ukuran maksimal: 2 MB per file</li>
                                    <li>Pastikan dokumen terbaca dengan jelas</li>
                                    <li>Dokumen bertanda <span class="text-red-500">*</span> wajib diupload</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Documents Grid -->
                    <div class="grid md:grid-cols-2 gap-6">
                        @foreach($jenisDokumen as $jenis => $info)
                            @php
                                $uploaded = $uploadedDokumen->get($jenis);
                            @endphp
                            <div class="border border-gray-200 rounded-lg p-4 document-card" data-jenis="{{ $jenis }}">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h4 class="font-semibold text-gray-800">
                                            {{ $info['nama'] }}
                                            @if($info['wajib'])
                                                <span class="text-red-500">*</span>
                                            @endif
                                        </h4>
                                        <p class="text-xs text-gray-500">{{ $info['deskripsi'] }}</p>
                                    </div>
                                    @if($uploaded)
                                        <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full">
                                            <i class="fas fa-check mr-1"></i>Uploaded
                                        </span>
                                    @endif
                                </div>

                                <!-- Upload Area -->
                                <div class="upload-area border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-blue-400 transition cursor-pointer {{ $uploaded ? 'hidden' : '' }}"
                                     onclick="document.getElementById('file_{{ $jenis }}').click()">
                                    <input type="file" 
                                           id="file_{{ $jenis }}" 
                                           class="hidden file-input"
                                           accept=".jpg,.jpeg,.png,.pdf"
                                           data-jenis="{{ $jenis }}">
                                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                    <p class="text-sm text-gray-600">Klik untuk upload</p>
                                    <p class="text-xs text-gray-400">atau drag & drop file</p>
                                </div>

                                <!-- Preview Area -->
                                <div class="preview-area {{ $uploaded ? '' : 'hidden' }}">
                                    @if($uploaded)
                                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                                            <div class="flex items-center">
                                                @if($uploaded->isImage())
                                                    <img src="{{ $uploaded->file_url }}" class="w-12 h-12 object-cover rounded mr-3">
                                                @else
                                                    <div class="w-12 h-12 bg-red-100 rounded flex items-center justify-center mr-3">
                                                        <i class="fas fa-file-pdf text-red-500 text-xl"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <p class="text-sm font-medium text-gray-800 truncate max-w-[150px]" title="{{ $uploaded->nama_file }}">{{ $uploaded->nama_file }}</p>
                                                    <p class="text-xs text-gray-500">{{ $uploaded->formatted_size }}</p>
                                                </div>
                                            </div>
                                            <div class="flex space-x-2">
                                                <a href="{{ $uploaded->file_url }}" target="_blank" class="p-2 text-blue-500 hover:bg-blue-50 rounded">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button" class="p-2 text-red-500 hover:bg-red-50 rounded btn-delete" data-id="{{ $uploaded->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Progress Bar -->
                                <div class="progress-bar hidden mt-3">
                                    <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-blue-500 rounded-full transition-all duration-300" style="width: 0%"></div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1 text-center">Uploading...</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Buttons -->
                    <div class="flex justify-between mt-8">
                        <a href="{{ route('ppdb.pendaftaran.step3') }}" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Kembali
                        </a>
                        <button type="submit" class="px-8 py-3 bg-blue-500 text-white rounded-lg font-semibold hover:bg-blue-600 transition">
                            Simpan & Lanjutkan
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // File input change handler
    document.querySelectorAll('.file-input').forEach(function(input) {
        input.addEventListener('change', function(e) {
            if (this.files.length > 0) {
                uploadFile(this.files[0], this.dataset.jenis);
            }
        });
    });

    // Upload file function
    function uploadFile(file, jenis) {
        const card = document.querySelector(`.document-card[data-jenis="${jenis}"]`);
        const uploadArea = card.querySelector('.upload-area');
        const previewArea = card.querySelector('.preview-area');
        const progressBar = card.querySelector('.progress-bar');
        const progressFill = progressBar.querySelector('div > div');

        // Validate file size
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file maksimal 2 MB');
            return;
        }

        // Show progress
        uploadArea.classList.add('hidden');
        progressBar.classList.remove('hidden');

        const formData = new FormData();
        formData.append('file', file);
        formData.append('jenis_dokumen', jenis);
        formData.append('_token', csrfToken);

        const xhr = new XMLHttpRequest();
        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percent = (e.loaded / e.total) * 100;
                progressFill.style.width = percent + '%';
            }
        });

        xhr.addEventListener('load', function() {
            progressBar.classList.add('hidden');
            
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                showPreview(card, response.dokumen, jenis);
            } else {
                uploadArea.classList.remove('hidden');
                const error = JSON.parse(xhr.responseText);
                alert(error.error || 'Upload gagal');
            }
        });

        xhr.addEventListener('error', function() {
            progressBar.classList.add('hidden');
            uploadArea.classList.remove('hidden');
            alert('Upload gagal. Silakan coba lagi.');
        });

        xhr.open('POST', '{{ route("ppdb.pendaftaran.upload-dokumen") }}');
        xhr.send(formData);
    }

    // Show preview after upload
    function showPreview(card, dokumen, jenis) {
        const previewArea = card.querySelector('.preview-area');
        const isImage = dokumen.nama_file.match(/\.(jpg|jpeg|png)$/i);
        
        previewArea.innerHTML = `
            <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                <div class="flex items-center">
                    ${isImage ? 
                        `<img src="${dokumen.url}" class="w-12 h-12 object-cover rounded mr-3">` :
                        `<div class="w-12 h-12 bg-red-100 rounded flex items-center justify-center mr-3">
                            <i class="fas fa-file-pdf text-red-500 text-xl"></i>
                        </div>`
                    }
                    <div>
                        <p class="text-sm font-medium text-gray-800 truncate max-w-[150px]" title="${dokumen.nama_file}">${dokumen.nama_file}</p>
                        <p class="text-xs text-gray-500">${dokumen.size}</p>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <a href="${dokumen.url}" target="_blank" class="p-2 text-blue-500 hover:bg-blue-50 rounded">
                        <i class="fas fa-eye"></i>
                    </a>
                    <button type="button" class="p-2 text-red-500 hover:bg-red-50 rounded btn-delete" data-id="${dokumen.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        previewArea.classList.remove('hidden');

        // Update card status
        const statusBadge = card.querySelector('.px-2');
        if (!statusBadge) {
            const header = card.querySelector('.flex.justify-between');
            const badge = document.createElement('span');
            badge.className = 'px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full';
            badge.innerHTML = '<i class="fas fa-check mr-1"></i>Uploaded';
            header.appendChild(badge);
        }

        // Attach delete handler
        attachDeleteHandler(previewArea.querySelector('.btn-delete'));
    }

    // Delete handler
    function attachDeleteHandler(button) {
        button.addEventListener('click', function() {
            if (!confirm('Hapus dokumen ini?')) return;
            
            const id = this.dataset.id;
            const card = this.closest('.document-card');
            const jenis = card.dataset.jenis;

            fetch(`/ppdb/pendaftaran/delete-dokumen/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    card.querySelector('.preview-area').classList.add('hidden');
                    card.querySelector('.preview-area').innerHTML = '';
                    card.querySelector('.upload-area').classList.remove('hidden');
                    
                    const badge = card.querySelector('.bg-green-100');
                    if (badge) badge.remove();
                }
            })
            .catch(error => {
                alert('Gagal menghapus dokumen');
            });
        });
    }

    // Attach delete handlers to existing buttons
    document.querySelectorAll('.btn-delete').forEach(attachDeleteHandler);

    // Drag and drop
    document.querySelectorAll('.upload-area').forEach(function(area) {
        area.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('border-blue-500', 'bg-blue-50');
        });

        area.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('border-blue-500', 'bg-blue-50');
        });

        area.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('border-blue-500', 'bg-blue-50');
            
            const card = this.closest('.document-card');
            const jenis = card.dataset.jenis;
            
            if (e.dataTransfer.files.length > 0) {
                uploadFile(e.dataTransfer.files[0], jenis);
            }
        });
    });
});
</script>
@endpush
