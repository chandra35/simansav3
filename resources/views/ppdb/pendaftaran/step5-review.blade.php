@extends('ppdb.layouts.app')

@section('title', 'Step 5 - Review Pendaftaran')

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
                    $currentStep = 5;
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
                    <h1 class="text-2xl font-bold text-white">Review & Kirim Pendaftaran</h1>
                    <p class="text-white opacity-90 mt-1">No. Pendaftaran: <strong>{{ $pendaftaran->nomor_pendaftaran }}</strong></p>
                </div>

                <div class="p-8">
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

                    <!-- Warning Box -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <i class="fas fa-exclamation-triangle text-yellow-500 mt-0.5 mr-3"></i>
                            <div class="text-sm text-yellow-700">
                                <p class="font-semibold mb-1">Perhatian!</p>
                                <p>Periksa kembali data Anda dengan teliti sebelum mengirim pendaftaran. Setelah dikirim, data tidak dapat diubah kecuali ditolak oleh panitia.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Data Calon Siswa -->
                    <div class="mb-8">
                        <div class="flex justify-between items-center mb-4 pb-2 border-b">
                            <h3 class="text-lg font-semibold text-gray-800">
                                <i class="fas fa-user text-blue-500 mr-2"></i>
                                Data Calon Siswa
                            </h3>
                            <a href="{{ route('ppdb.pendaftaran.step2') }}" class="text-sm text-blue-500 hover:underline">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </a>
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">NISN:</span>
                                <span class="font-medium text-gray-800 ml-2">{{ $pendaftaran->nisn }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">NIK:</span>
                                <span class="font-medium text-gray-800 ml-2">{{ $pendaftaran->nik }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Nama Lengkap:</span>
                                <span class="font-medium text-gray-800 ml-2">{{ $pendaftaran->nama_lengkap }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Jenis Kelamin:</span>
                                <span class="font-medium text-gray-800 ml-2">{{ $pendaftaran->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">TTL:</span>
                                <span class="font-medium text-gray-800 ml-2">{{ $pendaftaran->tempat_lahir }}, {{ $pendaftaran->tanggal_lahir?->format('d M Y') }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Agama:</span>
                                <span class="font-medium text-gray-800 ml-2">{{ ucfirst($pendaftaran->agama) }}</span>
                            </div>
                            <div class="md:col-span-2">
                                <span class="text-gray-500">Alamat:</span>
                                <span class="font-medium text-gray-800 ml-2">
                                    {{ $pendaftaran->alamat }}
                                    @if($pendaftaran->rt || $pendaftaran->rw), RT {{ $pendaftaran->rt }}/RW {{ $pendaftaran->rw }}@endif
                                    , {{ $pendaftaran->kelurahan }}, {{ $pendaftaran->kecamatan }}, {{ $pendaftaran->kabupaten }}, {{ $pendaftaran->provinsi }}
                                    @if($pendaftaran->kode_pos) {{ $pendaftaran->kode_pos }}@endif
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-500">No. HP:</span>
                                <span class="font-medium text-gray-800 ml-2">{{ $pendaftaran->no_hp }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Email:</span>
                                <span class="font-medium text-gray-800 ml-2">{{ $pendaftaran->email ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Data Asal Sekolah -->
                    <div class="mb-8">
                        <div class="flex justify-between items-center mb-4 pb-2 border-b">
                            <h3 class="text-lg font-semibold text-gray-800">
                                <i class="fas fa-school text-green-500 mr-2"></i>
                                Data Asal Sekolah
                            </h3>
                            <a href="{{ route('ppdb.pendaftaran.step2') }}" class="text-sm text-blue-500 hover:underline">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </a>
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Asal Sekolah:</span>
                                <span class="font-medium text-gray-800 ml-2">{{ $pendaftaran->asal_sekolah }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">NPSN:</span>
                                <span class="font-medium text-gray-800 ml-2">{{ $pendaftaran->npsn_asal_sekolah ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Tahun Lulus:</span>
                                <span class="font-medium text-gray-800 ml-2">{{ $pendaftaran->tahun_lulus }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Pilihan Jurusan -->
                    <div class="mb-8">
                        <div class="flex justify-between items-center mb-4 pb-2 border-b">
                            <h3 class="text-lg font-semibold text-gray-800">
                                <i class="fas fa-graduation-cap text-purple-500 mr-2"></i>
                                Pilihan Jurusan
                            </h3>
                            <a href="{{ route('ppdb.pendaftaran.step2') }}" class="text-sm text-blue-500 hover:underline">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </a>
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Pilihan 1:</span>
                                <span class="font-medium text-gray-800 ml-2">{{ $jurusanPilihan1?->nama ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Pilihan 2:</span>
                                <span class="font-medium text-gray-800 ml-2">{{ $jurusanPilihan2?->nama ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Jalur Pendaftaran:</span>
                                <span class="font-medium text-gray-800 ml-2">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs">
                                        {{ ucfirst($pendaftaran->jalur_pendaftaran) }}
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Data Orang Tua -->
                    <div class="mb-8">
                        <div class="flex justify-between items-center mb-4 pb-2 border-b">
                            <h3 class="text-lg font-semibold text-gray-800">
                                <i class="fas fa-users text-orange-500 mr-2"></i>
                                Data Orang Tua/Wali
                            </h3>
                            <a href="{{ route('ppdb.pendaftaran.step3') }}" class="text-sm text-blue-500 hover:underline">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </a>
                        </div>

                        @php
                            $pekerjaanOptions = \App\Models\PendaftaranPpdb::getPekerjaanOptions();
                            $penghasilanOptions = \App\Models\PendaftaranPpdb::getPenghasilanOptions();
                        @endphp
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Ayah -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="font-semibold text-gray-800 mb-3">
                                    <i class="fas fa-male text-blue-500 mr-1"></i> Ayah
                                </h4>
                                <div class="space-y-2 text-sm">
                                    <div><span class="text-gray-500">Nama:</span> <span class="font-medium">{{ $pendaftaran->nama_ayah }}</span></div>
                                    <div><span class="text-gray-500">Pekerjaan:</span> <span class="font-medium">{{ $pekerjaanOptions[$pendaftaran->pekerjaan_ayah] ?? '-' }}</span></div>
                                    <div><span class="text-gray-500">Penghasilan:</span> <span class="font-medium">{{ $penghasilanOptions[$pendaftaran->penghasilan_ayah] ?? '-' }}</span></div>
                                    <div><span class="text-gray-500">No. HP:</span> <span class="font-medium">{{ $pendaftaran->no_hp_ayah ?? '-' }}</span></div>
                                </div>
                            </div>

                            <!-- Ibu -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="font-semibold text-gray-800 mb-3">
                                    <i class="fas fa-female text-pink-500 mr-1"></i> Ibu
                                </h4>
                                <div class="space-y-2 text-sm">
                                    <div><span class="text-gray-500">Nama:</span> <span class="font-medium">{{ $pendaftaran->nama_ibu }}</span></div>
                                    <div><span class="text-gray-500">Pekerjaan:</span> <span class="font-medium">{{ $pekerjaanOptions[$pendaftaran->pekerjaan_ibu] ?? '-' }}</span></div>
                                    <div><span class="text-gray-500">Penghasilan:</span> <span class="font-medium">{{ $penghasilanOptions[$pendaftaran->penghasilan_ibu] ?? '-' }}</span></div>
                                    <div><span class="text-gray-500">No. HP:</span> <span class="font-medium">{{ $pendaftaran->no_hp_ibu ?? '-' }}</span></div>
                                </div>
                            </div>
                        </div>

                        @if($pendaftaran->nama_wali)
                            <div class="mt-4 bg-gray-50 rounded-lg p-4">
                                <h4 class="font-semibold text-gray-800 mb-3">
                                    <i class="fas fa-user-friends text-green-500 mr-1"></i> Wali
                                </h4>
                                <div class="grid md:grid-cols-2 gap-2 text-sm">
                                    <div><span class="text-gray-500">Nama:</span> <span class="font-medium">{{ $pendaftaran->nama_wali }}</span></div>
                                    <div><span class="text-gray-500">Hubungan:</span> <span class="font-medium">{{ ucfirst($pendaftaran->hubungan_wali) }}</span></div>
                                    <div><span class="text-gray-500">Pekerjaan:</span> <span class="font-medium">{{ $pekerjaanOptions[$pendaftaran->pekerjaan_wali] ?? '-' }}</span></div>
                                    <div><span class="text-gray-500">No. HP:</span> <span class="font-medium">{{ $pendaftaran->no_hp_wali ?? '-' }}</span></div>
                                </div>
                            </div>
                        @endif

                        <div class="mt-4 text-sm">
                            <span class="text-gray-500">Alamat Orang Tua:</span>
                            <span class="font-medium text-gray-800 ml-2">{{ $pendaftaran->alamat_orangtua }}</span>
                        </div>
                    </div>

                    <!-- Section: Dokumen -->
                    <div class="mb-8">
                        <div class="flex justify-between items-center mb-4 pb-2 border-b">
                            <h3 class="text-lg font-semibold text-gray-800">
                                <i class="fas fa-file-alt text-red-500 mr-2"></i>
                                Dokumen Terupload
                            </h3>
                            <a href="{{ route('ppdb.pendaftaran.step4') }}" class="text-sm text-blue-500 hover:underline">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </a>
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-4">
                            @foreach($pendaftaran->dokumen as $doc)
                                <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                                    <div class="flex items-center">
                                        @if($doc->isImage())
                                            <img src="{{ $doc->file_url }}" class="w-10 h-10 object-cover rounded mr-3">
                                        @else
                                            <div class="w-10 h-10 bg-red-100 rounded flex items-center justify-center mr-3">
                                                <i class="fas fa-file-pdf text-red-500"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="text-sm font-medium text-gray-800">{{ $jenisDokumen[$doc->jenis_dokumen]['nama'] ?? $doc->jenis_dokumen }}</p>
                                            <p class="text-xs text-gray-500">{{ $doc->formatted_size }}</p>
                                        </div>
                                    </div>
                                    <a href="{{ $doc->file_url }}" target="_blank" class="text-blue-500 hover:text-blue-700">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Confirmation -->
                    <form action="{{ route('ppdb.pendaftaran.submit') }}" method="POST" id="submitForm">
                        @csrf
                        
                        <div class="bg-gray-50 rounded-lg p-6 mb-6">
                            <label class="flex items-start cursor-pointer">
                                <input type="checkbox" id="confirmCheck" class="mt-1 mr-3" required>
                                <span class="text-sm text-gray-700">
                                    Dengan ini saya menyatakan bahwa seluruh data yang saya isi adalah <strong>benar dan dapat dipertanggungjawabkan</strong>. 
                                    Saya bersedia menerima sanksi jika dikemudian hari ditemukan data yang tidak sesuai dengan dokumen asli.
                                </span>
                            </label>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-between">
                            <a href="{{ route('ppdb.pendaftaran.step4') }}" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Kembali
                            </a>
                            <button type="submit" 
                                    id="submitBtn"
                                    class="px-8 py-3 bg-green-500 text-white rounded-lg font-semibold hover:bg-green-600 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                    disabled>
                                <i class="fas fa-paper-plane mr-2"></i>
                                Kirim Pendaftaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('confirmCheck').addEventListener('change', function() {
        document.getElementById('submitBtn').disabled = !this.checked;
    });

    document.getElementById('submitForm').addEventListener('submit', function(e) {
        if (!confirm('Apakah Anda yakin ingin mengirim pendaftaran? Data tidak dapat diubah setelah dikirim.')) {
            e.preventDefault();
        }
    });
</script>
@endpush
