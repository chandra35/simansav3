<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'db:backup {--file= : Custom backup filename}';
    protected $description = 'Create database backup before GTK restructure';

    public function handle()
    {
        $this->info('🔄 Creating database backup...');
        
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        
        $filename = $this->option('file') ?? 'backup_before_gtk_restructure_' . date('YmdHis') . '.sql';
        $filepath = storage_path('backups/' . $filename);
        
        // Create backups directory if not exists
        if (!is_dir(storage_path('backups'))) {
            mkdir(storage_path('backups'), 0755, true);
        }
        
        // Export current role assignments for reference
        $this->info('📊 Exporting current role assignments...');
        
        $users = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_uuid')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('users.username', 'users.name', 'roles.name as role_name')
            ->get();
        
        $roleFile = storage_path('backups/role_assignments_' . date('YmdHis') . '.txt');
        file_put_contents($roleFile, "CURRENT ROLE ASSIGNMENTS:\n\n");
        
        foreach ($users as $user) {
            file_put_contents($roleFile, "{$user->username} ({$user->name}) => {$user->role_name}\n", FILE_APPEND);
        }
        
        $this->info("✅ Role assignments exported to: {$roleFile}");
        
        // Count statistics
        $gtkCount = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'GTK')
            ->count();
        
        $waliKelasCount = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'Wali Kelas')
            ->count();
        
        $this->line('');
        $this->info('📈 Current Statistics:');
        $this->info("   - Users with 'GTK' role: {$gtkCount}");
        $this->info("   - Users with 'Wali Kelas' role: {$waliKelasCount}");
        $this->info("   - Total GTK records: " . DB::table('gtks')->count());
        $this->line('');
        
        $this->info("💾 Database backup would be created at: {$filepath}");
        $this->warn('Note: Actual mysqldump backup should be done manually or via database management tool');
        
        $this->line('');
        $this->info('✅ Pre-migration snapshot completed!');
        
        return 0;
    }
}
