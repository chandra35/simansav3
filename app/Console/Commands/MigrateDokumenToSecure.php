<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DokumenSiswa;
use App\Models\TahunPelajaran;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MigrateDokumenToSecure extends Command
{
    protected $signature = 'dokumen:migrate-secure 
                            {--dry-run : Run without actually moving files}
                            {--force : Force migration without confirmation}';
    
    protected $description = 'Migrate existing dokumen files to secure UUID-based structure';

    public function handle()
    {
        $this->info('🔒 Secure Dokumen Migration Tool');
        $this->newLine();
        
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        
        // Get all dokumen
        $dokumen = DokumenSiswa::with(['siswa'])->get();
        
        if ($dokumen->isEmpty()) {
            $this->info('✅ No dokumen to migrate.');
            return 0;
        }
        
        $this->info("Found {$dokumen->count()} dokumen to migrate.");
        $this->newLine();
        
        if (!$force && !$dryRun) {
            if (!$this->confirm('This will rename and move files. Continue?')) {
                $this->warn('Migration cancelled.');
                return 0;
            }
        }
        
        if ($dryRun) {
            $this->warn('🧪 DRY RUN MODE - No files will be moved');
            $this->newLine();
        }
        
        $tahunPelajaranAktif = TahunPelajaran::where('is_active', true)->first();
        
        $progressBar = $this->output->createProgressBar($dokumen->count());
        $progressBar->start();
        
        $success = 0;
        $failed = 0;
        $skipped = 0;
        
        foreach ($dokumen as $doc) {
            $progressBar->advance();
            
            // Skip if already migrated (has UUID)
            if ($doc->file_uuid) {
                $skipped++;
                continue;
            }
            
            if (!$doc->siswa) {
                $this->newLine();
                $this->error("❌ Dokumen ID {$doc->id}: Siswa not found!");
                $failed++;
                continue;
            }
            
            try {
                // Generate UUID
                $uuid = Str::uuid()->toString();
                $extension = pathinfo($doc->file_path, PATHINFO_EXTENSION);
                
                // Old path (current)
                $oldPath = $doc->file_path;
                $oldFullPath = storage_path("app/public/{$oldPath}");
                
                // New path (secure: dokumen-siswa/NISN/UUID.ext)
                $newFolder = "dokumen-siswa/{$doc->siswa->nisn}";
                $newFileName = "{$uuid}.{$extension}";
                $newPath = "{$newFolder}/{$newFileName}";
                $newFullPath = storage_path("app/private/{$newPath}");
                
                if (!$dryRun) {
                    // Check if old file exists
                    if (!Storage::disk('public')->exists($oldPath)) {
                        $this->newLine();
                        $this->warn("⚠️  File not found: {$oldPath}");
                        $failed++;
                        continue;
                    }
                    
                    // Create new folder if not exists
                    Storage::disk('private')->makeDirectory($newFolder);
                    
                    // Copy file to new location (keep original for safety)
                    $fileContent = Storage::disk('public')->get($oldPath);
                    Storage::disk('private')->put($newPath, $fileContent);
                    
                    // Update database
                    $doc->update([
                        'file_uuid' => $uuid,
                        'file_path' => $newPath,
                        'original_name' => $doc->nama_file ?? basename($oldPath),
                        'tahun_pelajaran' => $doc->tahun_pelajaran ?? $tahunPelajaranAktif?->nama ?? date('Y') . '-' . (date('Y') + 1),
                        'kelas_id' => $doc->siswa->kelasAktif()->first()?->id,
                        'status' => 'approved', // Assume existing dokumen are approved
                    ]);
                    
                    // Delete old file (commented out for safety - uncomment after verification)
                    // Storage::disk('public')->delete($oldPath);
                }
                
                $success++;
                
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("❌ Dokumen ID {$doc->id}: " . $e->getMessage());
                $failed++;
            }
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        // Summary
        $this->info('📊 Migration Summary:');
        $this->table(
            ['Status', 'Count'],
            [
                ['✅ Success', $success],
                ['⏭️  Skipped (already migrated)', $skipped],
                ['❌ Failed', $failed],
                ['📁 Total', $dokumen->count()],
            ]
        );
        
        if ($dryRun) {
            $this->newLine();
            $this->warn('🧪 This was a DRY RUN. No files were actually moved.');
            $this->info('Run without --dry-run to execute migration.');
        } else {
            $this->newLine();
            $this->info('✅ Migration completed!');
            $this->newLine();
            $this->warn('⚠️  Old files in storage/app/public/dokumen-siswa/ are NOT deleted yet.');
            $this->warn('Please verify the migration first, then manually delete old files.');
        }
        
        return 0;
    }
}

