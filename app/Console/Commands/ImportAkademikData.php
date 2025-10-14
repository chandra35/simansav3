<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Kurikulum;
use App\Models\Jurusan;
use App\Models\TahunPelajaran;
use App\Models\Kelas;

class ImportAkademikData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'akademik:import-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import akademik data dari backup dengan UUID baru';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai import data akademik...');
        
        $backupPath = 'uuid_conversion_backup';
        
        // Mapping old ID to new UUID
        $kurikulumMapping = [];
        $jurusanMapping = [];
        $tahunPelajaranMapping = [];
        $kelasMapping = [];
        
        // 1. Import Kurikulum (no dependencies)
        $this->info('Importing kurikulum...');
        $kurikulumData = json_decode(Storage::get("$backupPath/kurikulum.json"), true);
        
        foreach ($kurikulumData as $record) {
            $oldId = $record['id'];
            $newId = Str::uuid()->toString();
            $kurikulumMapping[$oldId] = $newId;
            
            Kurikulum::create([
                'id' => $newId,
                'kode' => $record['kode'],
                'nama_kurikulum' => $record['nama_kurikulum'],
                'deskripsi' => $record['deskripsi'],
                'tahun_berlaku' => $record['tahun_berlaku'],
                'has_jurusan' => $record['has_jurusan'],
                'is_active' => $record['is_active'],
                'created_at' => $record['created_at'],
                'updated_at' => $record['updated_at'],
                'deleted_at' => $record['deleted_at'],
            ]);
            
            $this->line("  - {$record['nama_kurikulum']} (old ID: $oldId -> new UUID: $newId)");
        }
        
        // 2. Import Jurusan (depends on kurikulum)
        $this->info('Importing jurusan...');
        $jurusanData = json_decode(Storage::get("$backupPath/jurusan.json"), true);
        
        foreach ($jurusanData as $record) {
            $oldId = $record['id'];
            $newId = Str::uuid()->toString();
            $jurusanMapping[$oldId] = $newId;
            
            Jurusan::create([
                'id' => $newId,
                'kurikulum_id' => $kurikulumMapping[$record['kurikulum_id']] ?? null,
                'kode_jurusan' => $record['kode_jurusan'],
                'nama_jurusan' => $record['nama_jurusan'],
                'deskripsi' => $record['deskripsi'],
                'is_active' => $record['is_active'],
                'created_at' => $record['created_at'],
                'updated_at' => $record['updated_at'],
                'deleted_at' => $record['deleted_at'],
            ]);
            
            $this->line("  - {$record['nama_jurusan']} (old ID: $oldId -> new UUID: $newId)");
        }
        
        // 3. Import Tahun Pelajaran (depends on kurikulum)
        $this->info('Importing tahun pelajaran...');
        $tahunPelajaranData = json_decode(Storage::get("$backupPath/tahun_pelajaran.json"), true);
        
        foreach ($tahunPelajaranData as $record) {
            $oldId = $record['id'];
            $newId = Str::uuid()->toString();
            $tahunPelajaranMapping[$oldId] = $newId;
            
            TahunPelajaran::create([
                'id' => $newId,
                'kurikulum_id' => $kurikulumMapping[$record['kurikulum_id']] ?? null,
                'tahun_awal' => $record['tahun_awal'],
                'tahun_akhir' => $record['tahun_akhir'],
                'nama' => $record['nama'],
                'semester' => $record['semester'],
                'tanggal_mulai' => $record['tanggal_mulai'],
                'tanggal_selesai' => $record['tanggal_selesai'],
                'is_active' => $record['is_active'],
                'created_at' => $record['created_at'],
                'updated_at' => $record['updated_at'],
                'deleted_at' => $record['deleted_at'],
            ]);
            
            $this->line("  - {$record['nama']} (old ID: $oldId -> new UUID: $newId)");
        }
        
        // 4. Import Kelas (depends on tahun_pelajaran, kurikulum, jurusan)
        $this->info('Importing kelas...');
        $kelasData = json_decode(Storage::get("$backupPath/kelas.json"), true);
        
        foreach ($kelasData as $record) {
            $oldId = $record['id'];
            $newId = Str::uuid()->toString();
            $kelasMapping[$oldId] = $newId;
            
            Kelas::create([
                'id' => $newId,
                'tahun_pelajaran_id' => $tahunPelajaranMapping[$record['tahun_pelajaran_id']] ?? null,
                'kurikulum_id' => $kurikulumMapping[$record['kurikulum_id']] ?? null,
                'jurusan_id' => isset($record['jurusan_id']) ? ($jurusanMapping[$record['jurusan_id']] ?? null) : null,
                'tingkat' => $record['tingkat'],
                'kode_kelas' => $record['kode_kelas'],
                'nama_kelas' => $record['nama_kelas'],
                'wali_kelas_id' => $record['wali_kelas_id'], // This is already UUID
                'kapasitas' => $record['kapasitas'],
                'deskripsi' => $record['deskripsi'],
                'is_active' => $record['is_active'],
                'created_at' => $record['created_at'],
                'updated_at' => $record['updated_at'],
                'deleted_at' => $record['deleted_at'],
            ]);
            
            $this->line("  - {$record['nama_kelas']} (old ID: $oldId -> new UUID: $newId)");
        }
        
        // Note: siswa_kelas.json is empty (0 records), so we skip it
        
        $this->info('');
        $this->info('Summary:');
        $this->info("  - Kurikulum: " . count($kurikulumData) . " records");
        $this->info("  - Jurusan: " . count($jurusanData) . " records");
        $this->info("  - Tahun Pelajaran: " . count($tahunPelajaranData) . " records");
        $this->info("  - Kelas: " . count($kelasData) . " records");
        $this->info('');
        $this->info('✅ Import selesai!');
        
        return 0;
    }
}
