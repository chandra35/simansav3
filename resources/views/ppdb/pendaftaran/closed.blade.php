@extends('ppdb.layouts.app')

@section('title', 'Pendaftaran Ditutup')

@section('content')
<div class="min-h-screen bg-gray-100 flex items-center justify-center py-12 px-4">
    <div class="max-w-lg w-full">
        <div class="bg-white rounded-xl shadow-lg p-8 text-center">
            <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-calendar-times text-red-500 text-4xl"></i>
            </div>
            
            <h1 class="text-2xl font-bold text-gray-800 mb-4">Pendaftaran Belum/Sudah Ditutup</h1>
            
            <p class="text-gray-600 mb-6">
                Maaf, pendaftaran PPDB saat ini belum dibuka atau sudah ditutup.
            </p>

            @if($pengaturan->tanggal_buka && $pengaturan->tanggal_tutup)
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <p class="text-sm text-gray-600 mb-2">Periode Pendaftaran:</p>
                    <p class="font-semibold text-gray-800">
                        {{ $pengaturan->tanggal_buka->format('d M Y') }} - {{ $pengaturan->tanggal_tutup->format('d M Y') }}
                    </p>
                </div>
            @endif

            <div class="space-y-3">
                <a href="{{ route('ppdb.landing') }}" class="block w-full bg-blue-500 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-600 transition">
                    <i class="fas fa-home mr-2"></i>
                    Kembali ke Beranda
                </a>
                
                <a href="{{ route('ppdb.pendaftaran.cek-status') }}" class="block w-full bg-gray-100 text-gray-700 py-3 px-6 rounded-lg font-semibold hover:bg-gray-200 transition">
                    <i class="fas fa-search mr-2"></i>
                    Cek Status Pendaftaran
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
