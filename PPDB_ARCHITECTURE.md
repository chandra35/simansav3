# 🏗️ PPDB Architecture - Terpisah dengan Database Sharing

> Dokumentasi arsitektur PPDB sebagai Laravel project terpisah, tapi share database dengan SIMANSA

---

## 📁 Struktur Folder

```
d:\projek\
├── simansav3/              (SIMANSA v3 - existing)
│   ├── app/
│   ├── config/
│   ├── database/
│   ├── routes/
│   ├── resources/
│   ├── .env
│   ├── composer.json
│   └── artisan
│
└── ppdbv3/                 (PPDB V3 - NEW, terpisah)
    ├── app/
    ├── config/
    ├── database/
    ├── routes/
    ├── resources/
    ├── .env                (Connected to: simansav3 database)
    ├── .env.example
    ├── composer.json
    └── artisan
```

---

## 🗄️ Database Architecture

### **1 Database Shared (simansav3)**

```
┌─────────────────────────────────────────────┐
│        DATABASE: simansav3                  │
├─────────────────────────────────────────────┤
│                                             │
│  TABLES dari SIMANSA:                      │
│  • users                                   │
│  • gtk (Guru, Tenaga Kependidikan)         │
│  • kelas                                   │
│  • tahun_pelajaran                         │
│  • siswa                                   │
│  • ... (existing tables)                   │
│                                             │
│  TABLES BARU (untuk PPDB):                 │
│  • calon_siswa                             │
│  • calon_dokumen                           │
│  • ppdb_settings                           │
│  • ppdb_verifikator                        │
│                                             │
└─────────────────────────────────────────────┘
         ↑                          ↑
         │                          │
    SIMANSA Akses              PPDB Akses
```

---

## 🔌 Connection Configuration

### **SIMANSA (.env)**
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simansav3
DB_USERNAME=root
DB_PASSWORD=
```

### **PPDB (.env)**
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simansav3        ← SAME DATABASE
DB_USERNAME=root
DB_PASSWORD=
```

---

## 🔗 Relasi Antar Database (Cross-Database Relationships)

### **GTK dari SIMANSA - Ambil di PPDB**

```
PPDB App
└── app/Models/Verifikator.php

    public function gtk()
    {
        // Relasi ke tabel gtk di database yang sama
        // Mengakses GTK dari SIMANSA
        return $this->belongsTo(Gtk::class, 'gtk_id');
    }
```

**Query Example:**
```php
// Di PPDB, ambil GTK yang tersedia
$verifikator = Verifikator::with('gtk')->get();

// Output:
// Verifikator -> gtk (dari SIMANSA table gtk)
// ├─ gtk_id: 1
// ├─ gtk.nama: "Sri Handini"
// ├─ gtk.nip: "031234"
// └─ gtk.jabatan: "Guru Bahasa"
```

---

## 📡 API Integration Pattern

### **Architecture Pattern: API Bridge**

```
PPDB App                        SIMANSA App
┌──────────────┐               ┌──────────────┐
│ Controllers  │               │ Controllers  │
│ Routes       │               │ Routes       │
│ Models       │               │ Models       │
└──────┬───────┘               └──────┬───────┘
       │                              │
       └──────────────────┬───────────┘
                          │
                    Shared Database
                    (simansav3)
                          │
            ┌─────────────┼─────────────┐
            │             │             │
        gtk table      users       siswa table
        (SIMANSA)    (SIMANSA)   (SIMANSA)
        
        calon_siswa (PPDB)
        calon_dokumen (PPDB)
        ppdb_settings (PPDB)
```

---

## 🚀 Implementasi: Akses GTK di PPDB

### **1. Model GTK (reuse dari SIMANSA code)**

**SIMANSA: `app/Models/Gtk.php`**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Gtk extends Model
{
    use HasUuids;

    protected $table = 'gtk';
    protected $fillable = [
        'nama',
        'nip',
        'jabatan',
        'bidang_keahlian',
        'status',
    ];
}
```

**PPDB: `app/Models/Gtk.php` (same code, bisa copy paste)**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Gtk extends Model
{
    use HasUuids;

    protected $table = 'gtk';
    protected $fillable = [
        'nama',
        'nip',
        'jabatan',
        'bidang_keahlian',
        'status',
    ];
    
    // Relasi ke Verifikator di PPDB
    public function verifikator()
    {
        return $this->hasOne(Verifikator::class, 'gtk_id');
    }
}
```

---

### **2. Model Verifikator PPDB**

**PPDB: `app/Models/Verifikator.php`**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Verifikator extends Model
{
    use HasUuids;

    protected $table = 'ppdb_verifikator';
    
    protected $fillable = [
        'gtk_id',
        'ppdb_settings_id',
        'jenis_dokumen_aktif',
        'is_active',
    ];
    
    protected $casts = [
        'jenis_dokumen_aktif' => 'array',
    ];
    
    // Relasi ke GTK
    public function gtk()
    {
        return $this->belongsTo(Gtk::class, 'gtk_id');
    }
    
    // Relasi ke PPDB Settings
    public function ppdbSettings()
    {
        return $this->belongsTo(PpdbSettings::class, 'ppdb_settings_id');
    }
    
    // Relasi ke Calon Dokumen
    public function calonDokumen()
    {
        return $this->hasMany(CalonDokumen::class, 'verifikator_id');
    }
}
```

---

### **3. Model CalonDokumen PPDB**

**PPDB: `app/Models/CalonDokumen.php`**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CalonDokumen extends Model
{
    use HasUuids;

    protected $table = 'calon_dokumen';
    
    protected $fillable = [
        'calon_siswa_id',
        'jenis_dokumen',
        'file_path',
        'file_size',
        'file_type',
        'status_verifikasi',
        'verifikator_id',
        'catatan_verifikasi',
        'tanggal_verifikasi',
        'alasan_tolak',
    ];
    
    // Relasi ke Calon Siswa
    public function calonSiswa()
    {
        return $this->belongsTo(CalonSiswa::class, 'calon_siswa_id');
    }
    
    // Relasi ke Verifikator (GTK)
    public function verifikator()
    {
        return $this->belongsTo(Verifikator::class, 'verifikator_id');
    }
    
    // Untuk join ke GTK
    public function verifikatorGtk()
    {
        return $this->belongsTo(Gtk::class, 'verifikator_id');
    }
}
```

---

## 🔍 Query Examples: Bagaimana Mengakses Data Lintas Folder

### **Scenario 1: Admin PPDB - List GTK untuk assign verifikator**

**PPDB Controller: `app/Http/Controllers/Admin/PpdbVerifikatorController.php`**
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Models\Gtk;
use App\Models\Verifikator;

class PpdbVerifikatorController extends Controller
{
    public function index()
    {
        // Query GTK dari SIMANSA table (same database)
        $daftarGtk = Gtk::where('status', 'active')
                        ->select('id', 'nama', 'nip', 'jabatan')
                        ->get();
        
        // Query Verifikator yang sudah ditambahkan
        $verifikatorTerpilih = Verifikator::with('gtk')
                                          ->where('is_active', true)
                                          ->get();
        
        return view('admin.ppdb.verifikator', [
            'daftarGtk' => $daftarGtk,
            'verifikatorTerpilih' => $verifikatorTerpilih,
        ]);
    }
}
```

**View: `resources/views/admin/ppdb/verifikator.blade.php`**
```blade
@foreach($verifikatorTerpilih as $v)
    <tr>
        <td>{{ $v->gtk->nama }}</td>
        <td>{{ $v->gtk->nip }}</td>
        <td>{{ $v->gtk->jabatan }}</td>
        <td>
            @foreach($v->jenis_dokumen_aktif as $dok)
                <span class="badge">{{ $dok }}</span>
            @endforeach
        </td>
    </tr>
@endforeach
```

---

### **Scenario 2: Admin PPDB - Verifikasi dokumen & assign GTK**

**PPDB Controller: `app/Http/Controllers/Admin/PpdbPendaftarController.php`**
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Models\CalonDokumen;
use App\Models\Verifikator;

class PpdbPendaftarController extends Controller
{
    public function assignVerifikator($dokumentId)
    {
        $dokumen = CalonDokumen::findOrFail($dokumentId);
        
        // Get available verifikator untuk jenis dokumen ini
        $verifikatorTersedia = Verifikator::where('is_active', true)
                                         ->get()
                                         ->filter(function ($v) use ($dokumen) {
                                             return in_array(
                                                 $dokumen->jenis_dokumen,
                                                 $v->jenis_dokumen_aktif
                                             );
                                         });
        
        return view('admin.ppdb.assign-verifikator', [
            'dokumen' => $dokumen,
            'verifikatorTersedia' => $verifikatorTersedia,
        ]);
    }
    
    public function storeVerifikasi(Request $request, $dokumentId)
    {
        $dokumen = CalonDokumen::findOrFail($dokumentId);
        
        $dokumen->update([
            'verifikator_id' => $request->verifikator_id,
            'status_verifikasi' => 'pending',
        ]);
        
        // Email notification ke verifikator (GTK)
        $verifikator = Verifikator::find($request->verifikator_id);
        // $verifikator->gtk->email -> ambil email GTK dari SIMANSA
        
        return redirect()->back()->with('success', 'Verifikator assigned');
    }
}
```

---

### **Scenario 3: Verifikator (GTK) - Review dokumen**

**PPDB Controller: `app/Http/Controllers/Verifikator/DokumenController.php`**
```php
<?php

namespace App\Http\Controllers\Verifikator;

use App\Models\CalonDokumen;
use Auth;

class DokumenController extends Controller
{
    public function index()
    {
        // Ambil user yang login (dari SIMANSA users table)
        $user = Auth::user();
        
        // Cari Verifikator berdasarkan user
        // (Assume ada relation di User model ke Verifikator)
        $verifikator = $user->verifikator();
        
        if (!$verifikator) {
            abort(403, 'Anda bukan verifikator');
        }
        
        // Get dokumen yang ditugaskan ke verifikator ini
        $dokumenDitugaskan = CalonDokumen::where('verifikator_id', $verifikator->id)
                                        ->with('calonSiswa')
                                        ->where('status_verifikasi', 'pending')
                                        ->paginate(10);
        
        return view('verifikator.dokumen', [
            'dokumenDitugaskan' => $dokumenDitugaskan,
        ]);
    }
    
    public function approve($dokumentId)
    {
        $dokumen = CalonDokumen::findOrFail($dokumentId);
        
        // Verifikasi bahwa user adalah verifikator dokumen ini
        $this->authorizeVerifikator($dokumen);
        
        $dokumen->update([
            'status_verifikasi' => 'approved',
            'verifikator_id' => Auth::user()->gtk_id, // Gunakan GTK ID dari user
            'tanggal_verifikasi' => now(),
        ]);
        
        return redirect()->back()->with('success', 'Dokumen disetujui');
    }
}
```

---

## 📊 Database Diagram

```
SIMANSA Tables              PPDB Tables
──────────────              ───────────
users ◄──── (FK) ──────►┐
                         ├─► ppdb_settings
gtk ◄──── (FK) ──────►┐  │
                      └──► ppdb_verifikator ──┐
                                               ├─► calon_siswa ──┐
                                               │                 ├─► calon_dokumen
                                               └─────────────────┘
tahun_pelajaran ◄──── (FK) ──── calon_siswa

siswa (akan di-import dari calon_siswa)
├─ nisn
├─ nama
├─ kelas_id (FK → kelas)
└─ tahun_pelajaran_id (FK → tahun_pelajaran)
```

---

## 🔄 Data Flow: Import dari PPDB ke SIMANSA

```
PPDB App (folder ppdb-laravel)
│
├─ calon_siswa (status = "diterima")
├─ calon_dokumen (status = "approved")
│
│ (Query dari PPDB ke database simansav3)
│
└─ API Endpoint: POST /api/import/calon-siswa-diterima
   
   SIMANSA App (folder simansav3)
   │
   ├─ Receive data via API
   ├─ Transform data:
   │  ├─ calon_siswa.nisn → siswa.nisn
   │  ├─ calon_siswa.nama → siswa.nama
   │  └─ calon_siswa.kelas_id → siswa.kelas_id
   │
   └─ Insert ke siswa table (direct query ke database)
      
      Database simansav3
      │
      └─ siswa table (new records)
```

---

## 🛠️ Setup Steps: Bagaimana Setup Folder PPDB

### **Step 1: Create New Laravel Project**
```bash
cd d:\projek
laravel new ppdb-laravel
cd ppdb-laravel
```

### **Step 2: Copy API dari SIMANSA**
```
DARI: d:\projek\simansav3\app\Services\NisValidationService.php
KE:   d:\projek\ppdb-laravel\app\Services\NisValidationService.php

DARI: d:\projek\simansav3\app\Http\Controllers\Api\NisValidationController.php
KE:   d:\projek\ppdb-laravel\app\Http\Controllers\Api\NisValidationController.php
```

### **Step 3: Setup Database Connection (Same DB)**
Edit `.env` PPDB:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simansav3        ← SAME!
DB_USERNAME=root
DB_PASSWORD=
```

### **Step 4: Create PPDB Models**
```bash
php artisan make:model CalonSiswa -m
php artisan make:model CalonDokumen -m
php artisan make:model PpdbSettings -m
php artisan make:model Verifikator -m
php artisan make:model Gtk        # Link ke SIMANSA GTK
```

### **Step 5: Create PPDB Controllers**
```bash
php artisan make:controller Ppdb/RegisterController
php artisan make:controller Ppdb/DashboardController
php artisan make:controller Admin/PpdbSettingsController
php artisan make:controller Admin/PpdbPendaftarController
php artisan make:controller Admin/PpdbVerifikatorController
```

### **Step 6: Create PPDB Routes**
File: `routes/ppdb.php`
```php
Route::group(['prefix' => 'ppdb', 'as' => 'ppdb.'], function () {
    Route::get('/landing', 'Ppdb\LandingController@index')->name('landing');
    Route::post('/register', 'Ppdb\RegisterController@store')->name('register.store');
    Route::get('/dashboard', 'Ppdb\DashboardController@index')->name('dashboard');
});
```

### **Step 7: Run Migrations (PPDB Tables)**
```bash
php artisan migrate
```

---

## 🎯 Perbandingan File Antar Folder

### **Untuk Membandingkan API/Service Code:**

**Command: Buka 2 files side-by-side di VS Code**
```
SIMANSA File:  d:\projek\simansav3\app\Services\NisValidationService.php
PPDB File:     d:\projek\ppdb-laravel\app\Services\NisValidationService.php

(Bisa pakai VS Code: Ctrl+K Ctrl+O → Open to the Side)
```

**Atau gunakan Git/Diff untuk compare:**
```bash
# Compare files across folders
diff "d:\projek\simansav3\app\Services\NisValidationService.php" `
     "d:\projek\ppdb-laravel\app\Services\NisValidationService.php"
```

### **Recommended Approach: Copy & Adapt**
```
1. Copy file dari SIMANSA ke PPDB
2. Review code SIMANSA untuk memahami logic
3. Adapt untuk kebutuhan PPDB (kalau ada perbedaan)
4. Test untuk memastikan compatibility
```

---

## 📋 Files yang Bisa Di-Share (Copy Paste)

| File | Dari SIMANSA | Ke PPDB | Tujuan |
|------|-------------|---------|--------|
| `NisValidationService.php` | ✅ | ✅ | Validasi NISN ke Kemendikbud |
| `NisValidationController.php` | ✅ | ✅ | API endpoint validasi NISN |
| `Gtk.php` (Model) | ✅ | ✅ | Akses GTK dari SIMANSA |
| `TahunPelajaran.php` (Model) | ✅ | ✅ | Akses tahun pelajaran |
| `User.php` (Model) | ✅ | ✅ | Akses user & auth |
| Config auth | ✅ | ✅ (modify) | Konfigurasi authentication |

---

## ✅ Advantages Terpisah + Shared DB

| Aspek | Keuntungan |
|-------|-----------|
| **Independence** | Dua project terpisah, bisa develop/deploy sendiri-sendiri |
| **Reusability** | Copy-paste code dari SIMANSA ke PPDB |
| **Data Sharing** | Direct query ke 1 database (tidak perlu API kompleks) |
| **GTK Access** | Ambil langsung dari gtk table di SIMANSA |
| **Scalability** | Bisa upgrade PPDB tanpa affect SIMANSA |
| **Security** | Folder terpisah = version control terpisah |
| **Maintenance** | Dua codebase, bisa dikerjakan tim berbeda |

---

Jadi summary-nya:
- 🗂️ **2 Folder terpisah** (simansav3 & ppdb-laravel)
- 🗄️ **1 Database shared** (simansav3)
- 🔗 **Direct relasi antar table** (query langsung)
- 📋 **Copy-paste code** dari SIMANSA ke PPDB (API, services)
- 📊 **Compare file** via VS Code side-by-side atau diff command

Siap mulai setup folder PPDB? 🚀

