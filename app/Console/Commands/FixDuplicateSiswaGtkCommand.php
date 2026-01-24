<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Siswa;
use App\Models\Gtk;
use App\Models\User;

class FixDuplicateSiswaGtkCommand extends Command
{
    protected $signature = 'fix:duplicate-siswa-gtk {nisn?}';
    protected $description = 'Fix siswa records that have same NISN as GTK NIK';

    public function handle()
    {
        $nisn = $this->argument('nisn');

        if ($nisn) {
            // Fix specific NISN
            $this->fixSpecific($nisn);
        } else {
            // Find all duplicates
            $this->fixAll();
        }

        return 0;
    }

    private function fixSpecific($nisn)
    {
        $this->info("Mencari data dengan NISN/NIK: {$nisn}");

        // Check GTK
        $gtk = Gtk::where('nik', $nisn)->first();
        $this->line("GTK: " . ($gtk ? "FOUND ({$gtk->nama_lengkap})" : "NOT FOUND"));

        // Check Siswa
        $siswa = Siswa::where('nisn', $nisn)->first();
        $this->line("Siswa: " . ($siswa ? "FOUND ({$siswa->nama_lengkap})" : "NOT FOUND"));

        if ($gtk && $siswa) {
            $this->warn("DUPLICATE FOUND!");
            
            // Check if same user
            if ($gtk->user_id === $siswa->user_id) {
                $this->error("Same user! Deleting siswa record...");
                $siswaId = $siswa->id;
                $siswa->delete();
                $this->info("✓ Deleted siswa record (ID: {$siswaId})");
            } else {
                $this->warn("Different users! GTK User: {$gtk->user_id}, Siswa User: {$siswa->user_id}");
                
                // Check siswa's user
                $siswaUser = User::find($siswa->user_id);
                if ($siswaUser) {
                    $this->line("Siswa User: {$siswaUser->name} ({$siswaUser->username})");
                    
                    if ($this->confirm("Delete siswa record for '{$siswaUser->name}'?", true)) {
                        $siswaId = $siswa->id;
                        $siswa->delete();
                        $this->info("✓ Deleted siswa record (ID: {$siswaId})");
                        
                        // Remove Siswa role from user
                        if ($siswaUser->hasRole('Siswa')) {
                            $siswaUser->removeRole('Siswa');
                            $this->info("✓ Removed 'Siswa' role from user");
                        }
                    }
                }
            }
        } elseif ($gtk) {
            $this->info("✓ Only GTK exists - No action needed");
        } elseif ($siswa) {
            $this->warn("Only Siswa exists - Manual review needed");
        } else {
            $this->error("Not found in both tables!");
        }
    }

    private function fixAll()
    {
        $this->info("Mencari semua duplikat NISN/NIK antara Siswa dan GTK...");

        // Get all GTK NIKs
        $gtkNiks = Gtk::pluck('nik')->toArray();
        
        // Find siswa with same NISN
        $duplicates = Siswa::whereIn('nisn', $gtkNiks)->get();

        $this->line("Found: {$duplicates->count()} duplicate(s)");

        if ($duplicates->count() === 0) {
            $this->info("✓ No duplicates found!");
            return;
        }

        foreach ($duplicates as $siswa) {
            $this->line("");
            $this->warn("DUPLICATE: NISN {$siswa->nisn} - {$siswa->nama_lengkap}");
            
            $gtk = Gtk::where('nik', $siswa->nisn)->first();
            
            if ($gtk) {
                $this->line("  GTK: {$gtk->nama_lengkap} (User: {$gtk->user_id})");
                $this->line("  Siswa: {$siswa->nama_lengkap} (User: {$siswa->user_id})");
                
                // Delete siswa record
                $siswaId = $siswa->id;
                $siswa->delete();
                $this->info("  ✓ Deleted siswa record (ID: {$siswaId})");
                
                // Remove Siswa role if same user
                if ($gtk->user_id === $siswa->user_id) {
                    $user = User::find($gtk->user_id);
                    if ($user && $user->hasRole('Siswa')) {
                        $user->removeRole('Siswa');
                        $this->info("  ✓ Removed 'Siswa' role from user");
                    }
                }
            }
        }

        $this->line("");
        $this->info("✓ Done! Fixed {$duplicates->count()} duplicate(s)");
    }
}
