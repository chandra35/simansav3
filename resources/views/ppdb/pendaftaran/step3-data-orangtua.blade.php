@extends('ppdb.layouts.app')

@section('title', 'Step 3 - Data Orang Tua')

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
                    $currentStep = 3;
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
                    <h1 class="text-2xl font-bold text-white">Data Orang Tua/Wali</h1>
                    <p class="text-white opacity-90 mt-1">No. Pendaftaran: <strong>{{ $pendaftaran->nomor_pendaftaran }}</strong></p>
                </div>

                <form action="{{ route('ppdb.pendaftaran.process-step3') }}" method="POST" class="p-8">
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

                    @php
                        $pekerjaanOptions = \App\Models\PendaftaranPpdb::getPekerjaanOptions();
                        $penghasilanOptions = \App\Models\PendaftaranPpdb::getPenghasilanOptions();
                    @endphp

                    <!-- Section: Data Ayah -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">
                            <i class="fas fa-male text-blue-500 mr-2"></i>
                            Data Ayah Kandung
                        </h3>
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Nama Ayah -->
                            <div>
                                <label for="nama_ayah" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nama Lengkap Ayah <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="nama_ayah" id="nama_ayah" 
                                       value="{{ old('nama_ayah', $pendaftaran->nama_ayah) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                            </div>

                            <!-- NIK Ayah -->
                            <div>
                                <label for="nik_ayah" class="block text-sm font-medium text-gray-700 mb-2">NIK Ayah</label>
                                <input type="text" name="nik_ayah" id="nik_ayah" 
                                       value="{{ old('nik_ayah', $pendaftaran->nik_ayah) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="16 digit NIK" maxlength="16">
                            </div>

                            <!-- Pekerjaan Ayah -->
                            <div>
                                <label for="pekerjaan_ayah" class="block text-sm font-medium text-gray-700 mb-2">
                                    Pekerjaan Ayah <span class="text-red-500">*</span>
                                </label>
                                <select name="pekerjaan_ayah" id="pekerjaan_ayah" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                                    <option value="">Pilih Pekerjaan</option>
                                    @foreach($pekerjaanOptions as $key => $label)
                                        <option value="{{ $key }}" {{ old('pekerjaan_ayah', $pendaftaran->pekerjaan_ayah) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Penghasilan Ayah -->
                            <div>
                                <label for="penghasilan_ayah" class="block text-sm font-medium text-gray-700 mb-2">
                                    Penghasilan Ayah <span class="text-red-500">*</span>
                                </label>
                                <select name="penghasilan_ayah" id="penghasilan_ayah" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                                    <option value="">Pilih Penghasilan</option>
                                    @foreach($penghasilanOptions as $key => $label)
                                        <option value="{{ $key }}" {{ old('penghasilan_ayah', $pendaftaran->penghasilan_ayah) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- No HP Ayah -->
                            <div>
                                <label for="no_hp_ayah" class="block text-sm font-medium text-gray-700 mb-2">No. HP Ayah</label>
                                <input type="tel" name="no_hp_ayah" id="no_hp_ayah" 
                                       value="{{ old('no_hp_ayah', $pendaftaran->no_hp_ayah) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="08xxxxxxxxxx">
                            </div>
                        </div>
                    </div>

                    <!-- Section: Data Ibu -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">
                            <i class="fas fa-female text-pink-500 mr-2"></i>
                            Data Ibu Kandung
                        </h3>
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Nama Ibu -->
                            <div>
                                <label for="nama_ibu" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nama Lengkap Ibu <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="nama_ibu" id="nama_ibu" 
                                       value="{{ old('nama_ibu', $pendaftaran->nama_ibu) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                            </div>

                            <!-- NIK Ibu -->
                            <div>
                                <label for="nik_ibu" class="block text-sm font-medium text-gray-700 mb-2">NIK Ibu</label>
                                <input type="text" name="nik_ibu" id="nik_ibu" 
                                       value="{{ old('nik_ibu', $pendaftaran->nik_ibu) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="16 digit NIK" maxlength="16">
                            </div>

                            <!-- Pekerjaan Ibu -->
                            <div>
                                <label for="pekerjaan_ibu" class="block text-sm font-medium text-gray-700 mb-2">
                                    Pekerjaan Ibu <span class="text-red-500">*</span>
                                </label>
                                <select name="pekerjaan_ibu" id="pekerjaan_ibu" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                                    <option value="">Pilih Pekerjaan</option>
                                    @foreach($pekerjaanOptions as $key => $label)
                                        <option value="{{ $key }}" {{ old('pekerjaan_ibu', $pendaftaran->pekerjaan_ibu) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Penghasilan Ibu -->
                            <div>
                                <label for="penghasilan_ibu" class="block text-sm font-medium text-gray-700 mb-2">
                                    Penghasilan Ibu <span class="text-red-500">*</span>
                                </label>
                                <select name="penghasilan_ibu" id="penghasilan_ibu" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                                    <option value="">Pilih Penghasilan</option>
                                    @foreach($penghasilanOptions as $key => $label)
                                        <option value="{{ $key }}" {{ old('penghasilan_ibu', $pendaftaran->penghasilan_ibu) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- No HP Ibu -->
                            <div>
                                <label for="no_hp_ibu" class="block text-sm font-medium text-gray-700 mb-2">No. HP Ibu</label>
                                <input type="tel" name="no_hp_ibu" id="no_hp_ibu" 
                                       value="{{ old('no_hp_ibu', $pendaftaran->no_hp_ibu) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="08xxxxxxxxxx">
                            </div>
                        </div>
                    </div>

                    <!-- Section: Data Wali (Opsional) -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">
                            <i class="fas fa-user-friends text-green-500 mr-2"></i>
                            Data Wali <span class="text-sm font-normal text-gray-500">(Opsional - Isi jika berbeda dengan orang tua)</span>
                        </h3>
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Nama Wali -->
                            <div>
                                <label for="nama_wali" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap Wali</label>
                                <input type="text" name="nama_wali" id="nama_wali" 
                                       value="{{ old('nama_wali', $pendaftaran->nama_wali) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>

                            <!-- Hubungan Wali -->
                            <div>
                                <label for="hubungan_wali" class="block text-sm font-medium text-gray-700 mb-2">Hubungan dengan Siswa</label>
                                <select name="hubungan_wali" id="hubungan_wali" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="">Pilih Hubungan</option>
                                    @foreach(['kakek' => 'Kakek', 'nenek' => 'Nenek', 'paman' => 'Paman', 'bibi' => 'Bibi', 'kakak' => 'Kakak', 'lainnya' => 'Lainnya'] as $key => $label)
                                        <option value="{{ $key }}" {{ old('hubungan_wali', $pendaftaran->hubungan_wali) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- NIK Wali -->
                            <div>
                                <label for="nik_wali" class="block text-sm font-medium text-gray-700 mb-2">NIK Wali</label>
                                <input type="text" name="nik_wali" id="nik_wali" 
                                       value="{{ old('nik_wali', $pendaftaran->nik_wali) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="16 digit NIK" maxlength="16">
                            </div>

                            <!-- No HP Wali -->
                            <div>
                                <label for="no_hp_wali" class="block text-sm font-medium text-gray-700 mb-2">No. HP Wali</label>
                                <input type="tel" name="no_hp_wali" id="no_hp_wali" 
                                       value="{{ old('no_hp_wali', $pendaftaran->no_hp_wali) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="08xxxxxxxxxx">
                            </div>

                            <!-- Pekerjaan Wali -->
                            <div>
                                <label for="pekerjaan_wali" class="block text-sm font-medium text-gray-700 mb-2">Pekerjaan Wali</label>
                                <select name="pekerjaan_wali" id="pekerjaan_wali" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="">Pilih Pekerjaan</option>
                                    @foreach($pekerjaanOptions as $key => $label)
                                        <option value="{{ $key }}" {{ old('pekerjaan_wali', $pendaftaran->pekerjaan_wali) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Penghasilan Wali -->
                            <div>
                                <label for="penghasilan_wali" class="block text-sm font-medium text-gray-700 mb-2">Penghasilan Wali</label>
                                <select name="penghasilan_wali" id="penghasilan_wali" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="">Pilih Penghasilan</option>
                                    @foreach($penghasilanOptions as $key => $label)
                                        <option value="{{ $key }}" {{ old('penghasilan_wali', $pendaftaran->penghasilan_wali) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Alamat Orang Tua -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">
                            <i class="fas fa-home text-orange-500 mr-2"></i>
                            Alamat Orang Tua/Wali
                        </h3>
                        
                        <div>
                            <label for="alamat_orangtua" class="block text-sm font-medium text-gray-700 mb-2">
                                Alamat Lengkap <span class="text-red-500">*</span>
                            </label>
                            <textarea name="alamat_orangtua" id="alamat_orangtua" rows="3" 
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                      placeholder="Alamat lengkap tempat tinggal orang tua/wali" required>{{ old('alamat_orangtua', $pendaftaran->alamat_orangtua) }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Kosongkan jika sama dengan alamat calon siswa</p>
                        </div>

                        <div class="mt-4">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" id="sama_dengan_siswa" class="mr-2 rounded">
                                <span class="text-sm text-gray-700">Sama dengan alamat calon siswa</span>
                            </label>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex justify-between mt-8">
                        <a href="{{ route('ppdb.pendaftaran.step2') }}" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
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
    // Copy alamat siswa to alamat orangtua
    document.getElementById('sama_dengan_siswa').addEventListener('change', function() {
        const alamatOrangtua = document.getElementById('alamat_orangtua');
        if (this.checked) {
            // Get alamat from pendaftaran (passed from controller)
            alamatOrangtua.value = @json($pendaftaran->alamat ?? '');
        }
    });

    // Only allow numbers for NIK fields
    ['nik_ayah', 'nik_ibu', 'nik_wali'].forEach(function(id) {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
    });
</script>
@endpush
