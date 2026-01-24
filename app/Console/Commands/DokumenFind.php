<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Siswa;
use App\Models\DokumenSiswa;

class DokumenFind extends Command
{
    protected $signature = 'dokumen:find {nisn}';
    protected $description = 'Cari dan tampilkan dokumen siswa berdasarkan NISN';

    public function handle()
    {
        $nisn = $this->argument('nisn');
        $siswa = Siswa::where('nisn', $nisn)->first();
        
        if (!$siswa) {
            $this->error("❌ Siswa dengan NISN {$nisn} tidak ditemukan!");
            return 1;
        }
        
        $dokumen = DokumenSiswa::where('siswa_id', $siswa->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        if ($dokumen->isEmpty()) {
            $this->warn("⚠️  Siswa ini belum upload dokumen apapun.");
            $this->newLine();
            $this->line("Siswa: {$siswa->nama_lengkap}");
            $this->line("NISN: {$siswa->nisn}");
            $kelas = $siswa->kelasAktif()->first();
            $this->line("Kelas: " . ($kelas ? $kelas->nama_lengkap : 'Belum ada kelas'));
            return 0;
        }
        
        // Header
        $this->info("╔════════════════════════════════════════════════════════════╗");
        $this->info("║ Siswa: {$siswa->nama_lengkap}");
        $this->info("║ NISN: {$siswa->nisn}");
        $kelas = $siswa->kelasAktif()->first();
        $this->info("║ Kelas: " . ($kelas ? $kelas->nama_lengkap : 'Belum ada kelas'));
        $this->info("╚════════════════════════════════════════════════════════════╝");
        $this->newLine();
        
        $totalSize = $dokumen->sum('file_size');
        $this->line("<fg=bright-white>Dokumen ({$dokumen->count()} files, " . $this->formatBytes($totalSize) . "):</>");
        $this->newLine();
        
        // List dokumen
        foreach ($dokumen as $doc) {
            $icon = match(true) {
                str_contains($doc->mime_type, 'pdf') => '📄',
                str_contains($doc->mime_type, 'image') => '🖼️',
                default => '📋'
            };
            
            $statusColor = match($doc->status ?? 'approved') {
                'approved' => 'green',
                'pending' => 'yellow',
                'rejected' => 'red',
                default => 'white'
            };
            
            $this->line("{$icon} <fg=bright-white>" . ($doc->original_name ?? $doc->nama_file ?? 'Unknown') . "</>");
            $this->line("   └─ {$doc->jenis_dokumen} | " . $this->formatBytes($doc->file_size) . " | <fg={$statusColor}>" . ucfirst($doc->status ?? 'approved') . "</>");
            
            if ($doc->file_uuid) {
                $fileName = $doc->file_uuid . '.' . pathinfo($doc->file_path, PATHINFO_EXTENSION);
                $this->line("   └─ File: <fg=gray>{$fileName}</>");
                $this->line("   └─ Path: <fg=gray>storage/app/private/{$doc->file_path}</>");
            } else {
                $this->line("   └─ Path: <fg=gray>storage/app/public/{$doc->file_path}</> <fg=yellow>(Old format)</>");
            }
            
            $this->line("   └─ Upload: {$doc->created_at->format('Y-m-d H:i:s')} ({$doc->created_at->diffForHumans()})");
            
            if ($doc->access_count > 0) {
                $this->line("   └─ Diakses: {$doc->access_count}x" . ($doc->accessed_at ? " (terakhir: {$doc->accessed_at->diffForHumans()})" : ""));
            }
            
            $this->newLine();
        }
        
        return 0;
    }
    
    private function formatBytes($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }
}
