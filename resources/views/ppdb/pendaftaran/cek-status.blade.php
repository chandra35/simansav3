@extends('ppdb.layouts.app')

@section('title', 'Cek Status Pendaftaran')

@section('content')
<div class="min-h-screen bg-gray-100 py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-lg mx-auto">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-8 py-6">
                    <h1 class="text-2xl font-bold text-white text-center">
                        <i class="fas fa-search mr-2"></i>
                        Cek Status Pendaftaran
                    </h1>
                </div>

                <div class="p-8">
                    @if($errors->any())
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                @foreach($errors->all() as $error)
                                    {{ $error }}
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('ppdb.pendaftaran.process-cek-status') }}" method="POST">
                        @csrf

                        <!-- Tabs -->
                        <div class="flex border-b mb-6">
                            <button type="button" class="tab-btn flex-1 py-3 text-center border-b-2 border-blue-500 text-blue-600 font-semibold" data-tab="nomor">
                                Nomor Pendaftaran
                            </button>
                            <button type="button" class="tab-btn flex-1 py-3 text-center border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="nisn">
                                NISN
                            </button>
                        </div>

                        <!-- Tab Content: Nomor Pendaftaran -->
                        <div id="tab-nomor" class="tab-content">
                            <div class="mb-4">
                                <label for="nomor_pendaftaran" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nomor Pendaftaran
                                </label>
                                <input type="text" 
                                       name="nomor_pendaftaran" 
                                       id="nomor_pendaftaran" 
                                       value="{{ old('nomor_pendaftaran') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="Contoh: PPDB202500001">
                            </div>
                        </div>

                        <!-- Tab Content: NISN -->
                        <div id="tab-nisn" class="tab-content hidden">
                            <div class="mb-4">
                                <label for="nisn" class="block text-sm font-medium text-gray-700 mb-2">
                                    NISN
                                </label>
                                <input type="text" 
                                       name="nisn" 
                                       id="nisn" 
                                       value="{{ old('nisn') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="10 digit NISN"
                                       maxlength="10">
                            </div>
                        </div>

                        <!-- Tanggal Lahir (Common) -->
                        <div class="mb-6">
                            <label for="tanggal_lahir" class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Lahir <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   name="tanggal_lahir" 
                                   id="tanggal_lahir" 
                                   value="{{ old('tanggal_lahir') }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                   required>
                            <p class="text-xs text-gray-500 mt-1">Digunakan untuk verifikasi identitas</p>
                        </div>

                        <button type="submit" class="w-full bg-blue-500 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-600 transition">
                            <i class="fas fa-search mr-2"></i>
                            Cek Status
                        </button>
                    </form>

                    <div class="mt-6 text-center">
                        <a href="{{ route('ppdb.pendaftaran.index') }}" class="text-blue-500 hover:underline text-sm">
                            <i class="fas fa-arrow-left mr-1"></i>
                            Kembali ke Pendaftaran
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.tab-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const tab = this.dataset.tab;
            
            // Update button styles
            document.querySelectorAll('.tab-btn').forEach(function(b) {
                b.classList.remove('border-blue-500', 'text-blue-600', 'font-semibold');
                b.classList.add('border-transparent', 'text-gray-500');
            });
            this.classList.add('border-blue-500', 'text-blue-600', 'font-semibold');
            this.classList.remove('border-transparent', 'text-gray-500');
            
            // Show/hide content
            document.querySelectorAll('.tab-content').forEach(function(content) {
                content.classList.add('hidden');
            });
            document.getElementById('tab-' + tab).classList.remove('hidden');
            
            // Clear inputs
            if (tab === 'nomor') {
                document.getElementById('nisn').value = '';
            } else {
                document.getElementById('nomor_pendaftaran').value = '';
            }
        });
    });

    // Only allow numbers for NISN
    document.getElementById('nisn').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
</script>
@endpush
