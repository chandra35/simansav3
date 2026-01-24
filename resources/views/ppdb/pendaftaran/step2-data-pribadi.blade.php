@extends('ppdb.layouts.app')

@section('title', 'Step 2 - Data Pribadi')

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
                    $currentStep = 2;
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
                    <h1 class="text-2xl font-bold text-white">Data Pribadi</h1>
                    <p class="text-white opacity-90 mt-1">No. Pendaftaran: <strong>{{ $pendaftaran->nomor_pendaftaran }}</strong></p>
                </div>

                <form action="{{ route('ppdb.pendaftaran.process-step2') }}" method="POST" class="p-8">
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

                    <!-- Section: Identitas -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">
                            <i class="fas fa-user text-blue-500 mr-2"></i>
                            Data Identitas
                        </h3>
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- NISN (readonly) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">NISN</label>
                                <input type="text" value="{{ $pendaftaran->nisn }}" class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg" readonly>
                            </div>

                            <!-- NIK -->
                            <div>
                                <label for="nik" class="block text-sm font-medium text-gray-700 mb-2">
                                    NIK <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="nik" id="nik" 
                                       value="{{ old('nik', $pendaftaran->nik) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('nik') border-red-500 @enderror"
                                       placeholder="16 digit NIK" maxlength="16" required>
                            </div>

                            <!-- Nama (readonly) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                                <input type="text" value="{{ $pendaftaran->nama_lengkap }}" class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg" readonly>
                            </div>

                            <!-- Jenis Kelamin -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Jenis Kelamin <span class="text-red-500">*</span>
                                </label>
                                <div class="flex space-x-4 mt-2">
                                    <label class="flex items-center">
                                        <input type="radio" name="jenis_kelamin" value="L" 
                                               {{ old('jenis_kelamin', $pendaftaran->jenis_kelamin) == 'L' ? 'checked' : '' }}
                                               class="mr-2" required>
                                        <span>Laki-laki</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="jenis_kelamin" value="P" 
                                               {{ old('jenis_kelamin', $pendaftaran->jenis_kelamin) == 'P' ? 'checked' : '' }}
                                               class="mr-2">
                                        <span>Perempuan</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Tempat Lahir -->
                            <div>
                                <label for="tempat_lahir" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tempat Lahir <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="tempat_lahir" id="tempat_lahir" 
                                       value="{{ old('tempat_lahir', $pendaftaran->tempat_lahir) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                            </div>

                            <!-- Tanggal Lahir (readonly) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Lahir</label>
                                <input type="date" value="{{ $pendaftaran->tanggal_lahir?->format('Y-m-d') }}" class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg" readonly>
                            </div>

                            <!-- Agama -->
                            <div>
                                <label for="agama" class="block text-sm font-medium text-gray-700 mb-2">
                                    Agama <span class="text-red-500">*</span>
                                </label>
                                <select name="agama" id="agama" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                                    <option value="">Pilih Agama</option>
                                    @foreach(\App\Models\PendaftaranPpdb::getAgamaOptions() as $key => $label)
                                        <option value="{{ $key }}" {{ old('agama', $pendaftaran->agama) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- No HP -->
                            <div>
                                <label for="no_hp" class="block text-sm font-medium text-gray-700 mb-2">
                                    No. HP <span class="text-red-500">*</span>
                                </label>
                                <input type="tel" name="no_hp" id="no_hp" 
                                       value="{{ old('no_hp', $pendaftaran->no_hp) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="08xxxxxxxxxx" required>
                            </div>

                            <!-- Email -->
                            <div class="md:col-span-2">
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" name="email" id="email" 
                                       value="{{ old('email', $pendaftaran->email) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="contoh@email.com">
                            </div>
                        </div>
                    </div>

                    <!-- Section: Alamat -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">
                            <i class="fas fa-map-marker-alt text-blue-500 mr-2"></i>
                            Alamat Tempat Tinggal
                        </h3>
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Alamat -->
                            <div class="md:col-span-2">
                                <label for="alamat" class="block text-sm font-medium text-gray-700 mb-2">
                                    Alamat Lengkap <span class="text-red-500">*</span>
                                </label>
                                <textarea name="alamat" id="alamat" rows="3" 
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" 
                                          placeholder="Nama jalan, nomor rumah, dll" required>{{ old('alamat', $pendaftaran->alamat) }}</textarea>
                            </div>

                            <!-- RT/RW -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="rt" class="block text-sm font-medium text-gray-700 mb-2">RT</label>
                                    <input type="text" name="rt" id="rt" 
                                           value="{{ old('rt', $pendaftaran->rt) }}"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                           placeholder="001" maxlength="5">
                                </div>
                                <div>
                                    <label for="rw" class="block text-sm font-medium text-gray-700 mb-2">RW</label>
                                    <input type="text" name="rw" id="rw" 
                                           value="{{ old('rw', $pendaftaran->rw) }}"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                           placeholder="001" maxlength="5">
                                </div>
                            </div>

                            <!-- Kelurahan -->
                            <div>
                                <label for="kelurahan" class="block text-sm font-medium text-gray-700 mb-2">
                                    Kelurahan/Desa <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="kelurahan" id="kelurahan" 
                                       value="{{ old('kelurahan', $pendaftaran->kelurahan) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                            </div>

                            <!-- Kecamatan -->
                            <div>
                                <label for="kecamatan" class="block text-sm font-medium text-gray-700 mb-2">
                                    Kecamatan <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="kecamatan" id="kecamatan" 
                                       value="{{ old('kecamatan', $pendaftaran->kecamatan) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                            </div>

                            <!-- Kabupaten -->
                            <div>
                                <label for="kabupaten" class="block text-sm font-medium text-gray-700 mb-2">
                                    Kabupaten/Kota <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="kabupaten" id="kabupaten" 
                                       value="{{ old('kabupaten', $pendaftaran->kabupaten) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                            </div>

                            <!-- Provinsi -->
                            <div>
                                <label for="provinsi" class="block text-sm font-medium text-gray-700 mb-2">
                                    Provinsi <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="provinsi" id="provinsi" 
                                       value="{{ old('provinsi', $pendaftaran->provinsi) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                            </div>

                            <!-- Kode Pos -->
                            <div>
                                <label for="kode_pos" class="block text-sm font-medium text-gray-700 mb-2">Kode Pos</label>
                                <input type="text" name="kode_pos" id="kode_pos" 
                                       value="{{ old('kode_pos', $pendaftaran->kode_pos) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="12345" maxlength="10">
                            </div>
                        </div>
                    </div>

                    <!-- Section: Asal Sekolah -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">
                            <i class="fas fa-school text-blue-500 mr-2"></i>
                            Data Asal Sekolah
                        </h3>
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Nama Sekolah -->
                            <div>
                                <label for="asal_sekolah" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nama Sekolah Asal <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="asal_sekolah" id="asal_sekolah" 
                                       value="{{ old('asal_sekolah', $pendaftaran->asal_sekolah) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                            </div>

                            <!-- NPSN -->
                            <div>
                                <label for="npsn_asal_sekolah" class="block text-sm font-medium text-gray-700 mb-2">NPSN Sekolah</label>
                                <input type="text" name="npsn_asal_sekolah" id="npsn_asal_sekolah" 
                                       value="{{ old('npsn_asal_sekolah', $pendaftaran->npsn_asal_sekolah) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="8 digit NPSN" maxlength="20">
                            </div>

                            <!-- Alamat Sekolah -->
                            <div class="md:col-span-2">
                                <label for="alamat_asal_sekolah" class="block text-sm font-medium text-gray-700 mb-2">Alamat Sekolah</label>
                                <textarea name="alamat_asal_sekolah" id="alamat_asal_sekolah" rows="2" 
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('alamat_asal_sekolah', $pendaftaran->alamat_asal_sekolah) }}</textarea>
                            </div>

                            <!-- Tahun Lulus -->
                            <div>
                                <label for="tahun_lulus" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tahun Lulus <span class="text-red-500">*</span>
                                </label>
                                <select name="tahun_lulus" id="tahun_lulus" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                                    <option value="">Pilih Tahun</option>
                                    @for($y = date('Y') + 1; $y >= 2020; $y--)
                                        <option value="{{ $y }}" {{ old('tahun_lulus', $pendaftaran->tahun_lulus) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Pilihan Jurusan -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">
                            <i class="fas fa-graduation-cap text-blue-500 mr-2"></i>
                            Pilihan Jurusan & Jalur Pendaftaran
                        </h3>
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Jurusan Pilihan 1 -->
                            <div>
                                <label for="jurusan_pilihan_1" class="block text-sm font-medium text-gray-700 mb-2">
                                    Jurusan Pilihan 1 <span class="text-red-500">*</span>
                                </label>
                                <select name="jurusan_pilihan_1" id="jurusan_pilihan_1" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                                    <option value="">Pilih Jurusan</option>
                                    @foreach($jurusan as $j)
                                        <option value="{{ $j->id }}" {{ old('jurusan_pilihan_1', $pendaftaran->jurusan_pilihan_1) == $j->id ? 'selected' : '' }}>
                                            {{ $j->nama }} (Sisa: {{ $j->sisa_kuota }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Jurusan Pilihan 2 -->
                            <div>
                                <label for="jurusan_pilihan_2" class="block text-sm font-medium text-gray-700 mb-2">Jurusan Pilihan 2</label>
                                <select name="jurusan_pilihan_2" id="jurusan_pilihan_2" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="">Pilih Jurusan (opsional)</option>
                                    @foreach($jurusan as $j)
                                        <option value="{{ $j->id }}" {{ old('jurusan_pilihan_2', $pendaftaran->jurusan_pilihan_2) == $j->id ? 'selected' : '' }}>
                                            {{ $j->nama }} (Sisa: {{ $j->sisa_kuota }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Jalur Pendaftaran -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Jalur Pendaftaran <span class="text-red-500">*</span>
                                </label>
                                <div class="grid md:grid-cols-2 gap-4">
                                    @foreach(['reguler' => 'Jalur Reguler', 'prestasi' => 'Jalur Prestasi', 'afirmasi' => 'Jalur Afirmasi', 'zonasi' => 'Jalur Zonasi'] as $key => $label)
                                        <label class="flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                                            <input type="radio" name="jalur_pendaftaran" value="{{ $key }}" 
                                                   {{ old('jalur_pendaftaran', $pendaftaran->jalur_pendaftaran) == $key ? 'checked' : '' }}
                                                   class="mr-3" required>
                                            <span>{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex justify-between mt-8">
                        <a href="{{ route('ppdb.pendaftaran.step1') }}" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
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
    // Only allow numbers for NIK
    document.getElementById('nik').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
</script>
@endpush
