@extends('ppdb.layouts.app')

@section('title', 'Status Pendaftaran - ' . $pendaftaran->nomor_pendaftaran)

@section('content')
<div class="min-h-screen bg-gray-100 py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <!-- Header with Status -->
                <div class="px-8 py-6 {{ $pendaftaran->status == 'accepted' ? 'bg-gradient-to-r from-green-500 to-emerald-600' : ($pendaftaran->status == 'rejected' ? 'bg-gradient-to-r from-red-500 to-red-600' : 'bg-gradient-to-r from-blue-500 to-indigo-600') }}">
                    <div class="text-center text-white">
                        <p class="text-sm opacity-90">Status Pendaftaran</p>
                        <h1 class="text-2xl font-bold mt-1">
                            @switch($pendaftaran->status)
                                @case('draft')
                                    <i class="fas fa-edit mr-2"></i> Draft
                                    @break
                                @case('submitted')
                                    <i class="fas fa-clock mr-2"></i> Menunggu Verifikasi
                                    @break
                                @case('verified')
                                    <i class="fas fa-check-circle mr-2"></i> Terverifikasi
                                    @break
                                @case('rejected')
                                    <i class="fas fa-times-circle mr-2"></i> Ditolak
                                    @break
                                @case('accepted')
                                    <i class="fas fa-check-double mr-2"></i> Diterima
                                    @break
                                @case('enrolled')
                                    <i class="fas fa-user-graduate mr-2"></i> Terdaftar
                                    @break
                            @endswitch
                        </h1>
                    </div>
                </div>

                <div class="p-8">
                    <!-- Registration Info Card -->
                    <div class="bg-gray-50 rounded-lg p-6 mb-6">
                        <div class="text-center mb-4">
                            <p class="text-gray-600 text-sm">Nomor Pendaftaran</p>
                            <p class="text-2xl font-bold text-blue-600">{{ $pendaftaran->nomor_pendaftaran }}</p>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Nama:</span>
                                <span class="font-medium text-gray-800 block">{{ $pendaftaran->nama_lengkap }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">NISN:</span>
                                <span class="font-medium text-gray-800 block">{{ $pendaftaran->nisn }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Tanggal Daftar:</span>
                                <span class="font-medium text-gray-800 block">{{ $pendaftaran->created_at->format('d M Y, H:i') }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Jalur:</span>
                                <span class="font-medium text-gray-800 block">{{ ucfirst($pendaftaran->jalur_pendaftaran) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Status Timeline -->
                    <div class="mb-6">
                        <h3 class="font-semibold text-gray-800 mb-4">Progress Pendaftaran</h3>
                        
                        @php
                            $statusOrder = ['draft', 'submitted', 'verified', 'accepted', 'enrolled'];
                            $currentIndex = array_search($pendaftaran->status, $statusOrder);
                            if ($pendaftaran->status == 'rejected') {
                                $currentIndex = 2; // Show until verified step
                            }
                        @endphp
                        
                        <div class="relative">
                            @foreach([
                                ['status' => 'draft', 'label' => 'Pendaftaran Dibuat', 'icon' => 'fa-edit'],
                                ['status' => 'submitted', 'label' => 'Menunggu Verifikasi', 'icon' => 'fa-clock'],
                                ['status' => 'verified', 'label' => 'Terverifikasi', 'icon' => 'fa-check-circle'],
                                ['status' => 'accepted', 'label' => 'Diterima', 'icon' => 'fa-check-double'],
                                ['status' => 'enrolled', 'label' => 'Terdaftar', 'icon' => 'fa-user-graduate'],
                            ] as $index => $step)
                                @php
                                    $isPassed = $currentIndex >= $index;
                                    $isCurrent = $pendaftaran->status == $step['status'];
                                    $isRejected = $pendaftaran->status == 'rejected' && $step['status'] == 'verified';
                                @endphp
                                
                                <div class="flex items-center mb-4 last:mb-0">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0
                                        {{ $isRejected ? 'bg-red-500 text-white' : ($isPassed ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-500') }}">
                                        <i class="fas {{ $isRejected ? 'fa-times' : $step['icon'] }}"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="font-medium {{ $isPassed ? 'text-gray-800' : 'text-gray-500' }}">
                                            {{ $isRejected ? 'Ditolak' : $step['label'] }}
                                        </p>
                                        @if($isCurrent || $isRejected)
                                            <p class="text-xs text-gray-500">Status saat ini</p>
                                        @endif
                                    </div>
                                </div>
                                
                                @if($index < 4)
                                    <div class="ml-5 w-0.5 h-6 {{ $currentIndex > $index ? 'bg-green-500' : 'bg-gray-200' }}"></div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Rejection Note -->
                    @if($pendaftaran->status == 'rejected' && $pendaftaran->catatan_verifikasi)
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                            <div class="flex">
                                <i class="fas fa-exclamation-circle text-red-500 mt-0.5 mr-3"></i>
                                <div>
                                    <p class="font-semibold text-red-700 mb-1">Alasan Penolakan:</p>
                                    <p class="text-sm text-red-700">{{ $pendaftaran->catatan_verifikasi }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Accepted Info -->
                    @if($pendaftaran->status == 'accepted')
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                            <div class="flex">
                                <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3"></i>
                                <div>
                                    <p class="font-semibold text-green-700 mb-1">Selamat! Anda Diterima</p>
                                    @if($pendaftaran->diterima_di_jurusan)
                                        <p class="text-sm text-green-700">Jurusan: {{ $pendaftaran->diterima_di_jurusan }}</p>
                                    @endif
                                    <p class="text-sm text-green-700 mt-2">Silakan lakukan daftar ulang sesuai jadwal yang ditentukan.</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="space-y-3">
                        @if($pendaftaran->status == 'draft')
                            <a href="{{ route('ppdb.pendaftaran.continue', $pendaftaran->token) }}" 
                               class="block w-full bg-blue-500 text-white py-3 px-6 rounded-lg font-semibold text-center hover:bg-blue-600 transition">
                                <i class="fas fa-edit mr-2"></i>
                                Lanjutkan Pendaftaran
                            </a>
                        @endif

                        @if($pendaftaran->status == 'rejected')
                            <a href="{{ route('ppdb.pendaftaran.continue', $pendaftaran->token) }}" 
                               class="block w-full bg-orange-500 text-white py-3 px-6 rounded-lg font-semibold text-center hover:bg-orange-600 transition">
                                <i class="fas fa-redo mr-2"></i>
                                Perbaiki & Kirim Ulang
                            </a>
                        @endif
                        
                        <div class="grid grid-cols-2 gap-3">
                            <button onclick="window.print()" class="w-full bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-semibold hover:bg-gray-200 transition">
                                <i class="fas fa-print mr-2"></i>
                                Cetak
                            </button>
                            <a href="{{ route('ppdb.pendaftaran.cek-status') }}" class="w-full bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-semibold text-center hover:bg-gray-200 transition">
                                <i class="fas fa-search mr-2"></i>
                                Cek Lagi
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Back Link -->
            <div class="text-center mt-6">
                <a href="{{ route('ppdb.landing') }}" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
