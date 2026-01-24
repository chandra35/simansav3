@extends('ppdb.layouts.app')

@section('title', 'Step 1 - Validasi NISN')

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
                    $currentStep = 1;
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
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-8 py-6">
                    <h1 class="text-2xl font-bold text-white">Validasi NISN</h1>
                    <p class="text-white opacity-90 mt-1">Masukkan data untuk memulai pendaftaran</p>
                </div>

                <form action="{{ route('ppdb.pendaftaran.process-step1') }}" method="POST" class="p-8">
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

                    <div class="space-y-6">
                        <!-- NISN -->
                        <div>
                            <label for="nisn" class="block text-sm font-medium text-gray-700 mb-2">
                                NISN <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" 
                                       name="nisn" 
                                       id="nisn" 
                                       value="{{ old('nisn') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('nisn') border-red-500 @enderror"
                                       placeholder="Masukkan 10 digit NISN"
                                       maxlength="10"
                                       pattern="[0-9]{10}"
                                       required>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                                    <i class="fas fa-id-card text-gray-400"></i>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">NISN dapat dilihat di rapor atau kartu pelajar</p>
                        </div>

                        <!-- Nama Lengkap -->
                        <div>
                            <label for="nama_lengkap" class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="nama_lengkap" 
                                   id="nama_lengkap" 
                                   value="{{ old('nama_lengkap') }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('nama_lengkap') border-red-500 @enderror"
                                   placeholder="Masukkan nama sesuai akta kelahiran"
                                   required>
                            <p class="text-xs text-gray-500 mt-1">Nama harus sesuai dengan akta kelahiran</p>
                        </div>

                        <!-- Tanggal Lahir -->
                        <div>
                            <label for="tanggal_lahir" class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Lahir <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   name="tanggal_lahir" 
                                   id="tanggal_lahir" 
                                   value="{{ old('tanggal_lahir') }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('tanggal_lahir') border-red-500 @enderror"
                                   max="{{ date('Y-m-d') }}"
                                   required>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
                        <div class="flex">
                            <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
                            <div class="text-sm text-blue-700">
                                <p class="font-semibold mb-1">Informasi Penting:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Pastikan NISN yang dimasukkan benar</li>
                                    <li>Data yang dimasukkan akan divalidasi dengan database Kemendikbud</li>
                                    <li>Jika lupa NISN, silakan hubungi sekolah asal atau cek di <a href="https://nisn.data.kemdikbud.go.id" target="_blank" class="underline">nisn.data.kemdikbud.go.id</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex justify-between mt-8">
                        <a href="{{ route('ppdb.pendaftaran.index') }}" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Kembali
                        </a>
                        <button type="submit" class="px-8 py-3 bg-blue-500 text-white rounded-lg font-semibold hover:bg-blue-600 transition">
                            Lanjutkan
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
    // Only allow numbers for NISN
    document.getElementById('nisn').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
</script>
@endpush
