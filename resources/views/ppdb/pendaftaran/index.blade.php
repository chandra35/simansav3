@extends('ppdb.layouts.app')

@section('title', 'Pendaftaran PPDB')

@section('content')
<div class="bg-gradient-to-br from-blue-600 to-indigo-700 py-16">
    <div class="container mx-auto px-4">
        <div class="text-center text-white">
            <h1 class="text-4xl font-bold mb-4">Pendaftaran Peserta Didik Baru</h1>
            <p class="text-xl opacity-90">Tahun Pelajaran {{ date('Y') }}/{{ date('Y') + 1 }}</p>
        </div>
    </div>
</div>

<div class="container mx-auto px-4 py-12">
    <!-- Info Cards -->
    <div class="grid md:grid-cols-3 gap-6 mb-12">
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-blue-500 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="font-semibold text-gray-800">Periode Pendaftaran</h3>
                </div>
            </div>
            <p class="text-gray-600">
                @if($pengaturan->tanggal_buka && $pengaturan->tanggal_tutup)
                    {{ $pengaturan->tanggal_buka->format('d M Y') }} - {{ $pengaturan->tanggal_tutup->format('d M Y') }}
                @else
                    Lihat informasi selengkapnya
                @endif
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-green-500 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="font-semibold text-gray-800">Biaya Pendaftaran</h3>
                </div>
            </div>
            <p class="text-gray-600 text-lg font-semibold">
                @if($pengaturan->biaya_pendaftaran > 0)
                    {{ $pengaturan->formatted_biaya }}
                @else
                    GRATIS
                @endif
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-graduation-cap text-purple-500 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="font-semibold text-gray-800">Jurusan Tersedia</h3>
                </div>
            </div>
            <p class="text-gray-600">{{ $jurusan->count() }} Program Keahlian</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <!-- Alur Pendaftaran -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">
                    <i class="fas fa-tasks text-blue-500 mr-2"></i>
                    Alur Pendaftaran
                </h2>
                
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold shrink-0">1</div>
                        <div class="ml-4">
                            <h4 class="font-semibold text-gray-800">Validasi NISN</h4>
                            <p class="text-gray-600 text-sm">Masukkan NISN, nama lengkap, dan tanggal lahir untuk memulai pendaftaran</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold shrink-0">2</div>
                        <div class="ml-4">
                            <h4 class="font-semibold text-gray-800">Data Pribadi</h4>
                            <p class="text-gray-600 text-sm">Lengkapi data diri, alamat, dan pilih jurusan yang diminati</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold shrink-0">3</div>
                        <div class="ml-4">
                            <h4 class="font-semibold text-gray-800">Data Orang Tua/Wali</h4>
                            <p class="text-gray-600 text-sm">Lengkapi data ayah, ibu, dan wali (jika ada)</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold shrink-0">4</div>
                        <div class="ml-4">
                            <h4 class="font-semibold text-gray-800">Upload Dokumen</h4>
                            <p class="text-gray-600 text-sm">Upload dokumen persyaratan seperti KK, Akta, Rapor, dll</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold shrink-0">5</div>
                        <div class="ml-4">
                            <h4 class="font-semibold text-gray-800">Review & Kirim</h4>
                            <p class="text-gray-600 text-sm">Periksa kembali data dan kirim pendaftaran</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Persyaratan -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">
                    <i class="fas fa-clipboard-list text-green-500 mr-2"></i>
                    Persyaratan Pendaftaran
                </h2>
                
                @if($pengaturan->persyaratan)
                    <div class="prose max-w-none text-gray-600">
                        {!! nl2br(e($pengaturan->persyaratan)) !!}
                    </div>
                @else
                    <ul class="space-y-3 text-gray-600">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                            <span>Kartu Keluarga (KK) asli dan fotokopi</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                            <span>Akta Kelahiran asli dan fotokopi</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                            <span>Ijazah/SKL SMP/MTs atau sederajat</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                            <span>SKHUN atau pengganti</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                            <span>Rapor semester 1-5</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                            <span>Pas foto 3x4 cm background merah (3 lembar)</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                            <span>Surat Keterangan Sehat dari dokter</span>
                        </li>
                    </ul>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Action Card -->
            <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg p-8 text-white mb-6">
                <h3 class="text-xl font-bold mb-4">Siap Mendaftar?</h3>
                <p class="opacity-90 mb-6">Mulai pendaftaran online sekarang. Pastikan Anda sudah menyiapkan NISN dan dokumen-dokumen yang diperlukan.</p>
                <a href="{{ route('ppdb.pendaftaran.step1') }}" class="block w-full bg-white text-blue-600 py-3 px-6 rounded-lg font-semibold text-center hover:bg-gray-100 transition">
                    <i class="fas fa-edit mr-2"></i>
                    Mulai Pendaftaran
                </a>
            </div>

            <!-- Cek Status Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h3 class="font-bold text-gray-800 mb-4">
                    <i class="fas fa-search text-blue-500 mr-2"></i>
                    Cek Status Pendaftaran
                </h3>
                <p class="text-gray-600 text-sm mb-4">Sudah mendaftar? Cek status pendaftaran Anda di sini.</p>
                <a href="{{ route('ppdb.pendaftaran.cek-status') }}" class="block w-full bg-gray-100 text-gray-700 py-2 px-4 rounded-lg text-center hover:bg-gray-200 transition">
                    Cek Status
                </a>
            </div>

            <!-- Jurusan Card -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="font-bold text-gray-800 mb-4">
                    <i class="fas fa-graduation-cap text-purple-500 mr-2"></i>
                    Program Keahlian
                </h3>
                
                <div class="space-y-3">
                    @forelse($jurusan as $j)
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="font-semibold text-gray-800">{{ $j->nama }}</h4>
                                <span class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded">{{ $j->kode }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Kuota: {{ $j->kuota }}</span>
                                <span>Sisa: {{ $j->sisa_kuota }}</span>
                            </div>
                            <div class="mt-2 h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-500 rounded-full" style="width: {{ $j->persentase_terisi }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">Jurusan belum tersedia</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contact Section -->
@if($pengaturan->kontak_info)
<div class="bg-gray-100 py-12">
    <div class="container mx-auto px-4">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">
                <i class="fas fa-headset text-blue-500 mr-2"></i>
                Informasi & Bantuan
            </h2>
            <div class="prose max-w-none text-gray-600 text-center">
                {!! nl2br(e($pengaturan->kontak_info)) !!}
            </div>
        </div>
    </div>
</div>
@endif
@endsection
