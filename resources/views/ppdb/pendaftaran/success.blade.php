@extends('ppdb.layouts.app')

@section('title', 'Pendaftaran Berhasil')

@section('content')
<div class="min-h-screen bg-gray-100 flex items-center justify-center py-12 px-4">
    <div class="max-w-2xl w-full">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Success Header -->
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-8 py-12 text-center">
                <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-check-circle text-green-500 text-5xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Pendaftaran Berhasil!</h1>
                <p class="text-white opacity-90">Terima kasih telah mendaftar di sekolah kami</p>
            </div>

            <!-- Content -->
            <div class="p-8">
                <!-- Registration Info -->
                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <div class="text-center mb-4">
                        <p class="text-gray-600 text-sm">Nomor Pendaftaran Anda</p>
                        <p class="text-3xl font-bold text-blue-600 mt-1">{{ $pendaftaran->nomor_pendaftaran }}</p>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="text-center p-3 bg-white rounded-lg">
                            <p class="text-gray-500">Nama</p>
                            <p class="font-semibold text-gray-800">{{ $pendaftaran->nama_lengkap }}</p>
                        </div>
                        <div class="text-center p-3 bg-white rounded-lg">
                            <p class="text-gray-500">NISN</p>
                            <p class="font-semibold text-gray-800">{{ $pendaftaran->nisn }}</p>
                        </div>
                        <div class="text-center p-3 bg-white rounded-lg">
                            <p class="text-gray-500">Status</p>
                            <p class="font-semibold">
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs">
                                    {{ $pendaftaran->status_label }}
                                </span>
                            </p>
                        </div>
                        <div class="text-center p-3 bg-white rounded-lg">
                            <p class="text-gray-500">Tanggal Daftar</p>
                            <p class="font-semibold text-gray-800">{{ $pendaftaran->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Important Notice -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
                        <div class="text-sm text-blue-700">
                            <p class="font-semibold mb-2">Informasi Penting:</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li>Simpan nomor pendaftaran Anda untuk keperluan pengecekan status</li>
                                <li>Pendaftaran Anda akan diverifikasi oleh panitia dalam 1-3 hari kerja</li>
                                <li>Pantau status pendaftaran secara berkala</li>
                                <li>Siapkan dokumen asli untuk verifikasi jika diperlukan</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Next Steps -->
                <div class="border border-gray-200 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-gray-800 mb-3">
                        <i class="fas fa-list-ol text-blue-500 mr-2"></i>
                        Langkah Selanjutnya
                    </h3>
                    <ol class="list-decimal list-inside space-y-2 text-sm text-gray-600">
                        <li>Tunggu verifikasi dari panitia (cek status secara berkala)</li>
                        <li>Jika ada dokumen yang kurang, lengkapi sesuai instruksi</li>
                        <li>Setelah lolos verifikasi, tunggu pengumuman hasil seleksi</li>
                        <li>Jika diterima, lakukan daftar ulang sesuai jadwal</li>
                    </ol>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3">
                    <a href="{{ route('ppdb.pendaftaran.cek-status') }}" class="block w-full bg-blue-500 text-white py-3 px-6 rounded-lg font-semibold text-center hover:bg-blue-600 transition">
                        <i class="fas fa-search mr-2"></i>
                        Cek Status Pendaftaran
                    </a>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <button onclick="window.print()" class="w-full bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-semibold hover:bg-gray-200 transition">
                            <i class="fas fa-print mr-2"></i>
                            Cetak
                        </button>
                        <a href="{{ route('ppdb.landing') }}" class="w-full bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-semibold text-center hover:bg-gray-200 transition">
                            <i class="fas fa-home mr-2"></i>
                            Beranda
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Share Section -->
        <div class="mt-6 text-center">
            <p class="text-gray-500 text-sm mb-3">Bagikan info PPDB ke teman-temanmu</p>
            <div class="flex justify-center space-x-3">
                <a href="https://wa.me/?text=Aku%20sudah%20daftar%20PPDB%20di%20{{ urlencode(config('app.name')) }}!%20Daftar%20juga%20yuk%20di%20{{ urlencode(route('ppdb.landing')) }}" 
                   target="_blank"
                   class="w-10 h-10 bg-green-500 text-white rounded-full flex items-center justify-center hover:bg-green-600 transition">
                    <i class="fab fa-whatsapp"></i>
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('ppdb.landing')) }}" 
                   target="_blank"
                   class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center hover:bg-blue-700 transition">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://twitter.com/intent/tweet?text=Aku%20sudah%20daftar%20PPDB!%20Daftar%20juga%20di%20{{ urlencode(route('ppdb.landing')) }}" 
                   target="_blank"
                   class="w-10 h-10 bg-sky-500 text-white rounded-full flex items-center justify-center hover:bg-sky-600 transition">
                    <i class="fab fa-twitter"></i>
                </a>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .bg-white.rounded-xl, .bg-white.rounded-xl * {
            visibility: visible;
        }
        .bg-white.rounded-xl {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        button, a {
            display: none !important;
        }
    }
</style>
@endpush
@endsection
