# 📚 SIMANSA v3 - Development Guide

> Panduan lengkap untuk melanjutkan development project SIMANSA v3 di sesi baru atau komputer berbeda

## 📋 Project Information

- **Project Name:** SIMANSA v3 (Sistem Informasi Manajemen Siswa)
- **Framework:** Laravel 11
- **UI Template:** AdminLTE 3
- **Database:** MySQL dengan UUID primary keys
- **Repository:** [github.com/chandra35/simansav3](https://github.com/chandra35/simansav3)
- **Local Path:** `D:\projek\simansav3`

## 🎯 Latest Features

### Custom Menu System (Completed)
Sistem menu dinamis untuk siswa yang dapat dikelola oleh admin dengan fitur lengkap:

**Admin Features:**
- ✅ CRUD menu dengan TinyMCE rich text editor
- ✅ Dua tipe konten:
  - **General:** Konten sama untuk semua siswa
  - **Personal:** Konten berbeda per siswa dengan custom fields (username, password, dll)
- ✅ Menu grouping: Akademik, Administrasi, Hotspot & Akun, Lainnya
- ✅ Enable/Disable toggle untuk visibility menu
- ✅ Excel import untuk bulk assign siswa dengan template download
- ✅ DataTables dengan search untuk manage assigned students
- ✅ Assign siswa via: Excel upload, Manual selection, atau Filter by Kelas

**Siswa Features:**
- ✅ Dynamic sidebar dengan badge "NEW" untuk menu belum dibaca
- ✅ Menu dikelompokkan berdasarkan kategori
- ✅ Personal data display dengan copy buttons
- ✅ Password masking dengan toggle show/hide
- ✅ Auto mark as read ketika menu dibuka
- ✅ Badge "NEW" hilang otomatis setelah dibaca

## 📁 File Structure

### Backend Files

#### Controllers
```
app/Http/Controllers/
├── Admin/
│   └── CustomMenuController.php      # 13 methods (CRUD + extras)
└── Siswa/
    └── CustomMenuController.php      # 4 methods (index, show, markAsRead, getUnreadCount)
```

#### Models
```
app/Models/
├── CustomMenu.php                     # Menu model dengan relations dan helpers
└── CustomMenuSiswa.php               # Pivot model dengan tracking
```

#### Services
```
app/Services/
└── CustomMenuImportService.php       # Excel import/export dengan dynamic columns
```

#### Migrations
```
database/migrations/
├── 2025_10_30_100000_create_custom_menus_table.php
└── 2025_10_30_100001_create_custom_menu_siswa_table.php
```

### Frontend Files

#### Admin Views
```
resources/views/admin/custom-menu/
├── index.blade.php                    # List menu dengan DataTables
├── create.blade.php                   # Form create dengan TinyMCE
├── edit.blade.php                     # Form edit
└── assign.blade.php                   # Assign siswa (4 tabs)
```

#### Siswa Views
```
resources/views/siswa/custom-menu/
├── index.blade.php                    # Grouped menu cards dengan badge
└── show.blade.php                     # Detail menu dengan personal data
```

#### Additional Views
```
resources/views/siswa/profile/
└── change-password.blade.php          # Change password dengan strength indicator
```

### Configuration
```
config/adminlte.php                    # Modified: sidebar menu items
app/Providers/AppServiceProvider.php   # Modified: BuildingMenu event listener
routes/web.php                         # Modified: 16 new routes
```

## 🛣️ Routes

### Admin Routes (13 routes)
```php
Route::prefix('admin/custom-menu')->middleware(['auth', 'can:edit-siswa'])->group(function () {
    // CRUD
    Route::get('/', 'index')->name('admin.custom-menu.index');
    Route::get('/create', 'create')->name('admin.custom-menu.create');
    Route::post('/', 'store')->name('admin.custom-menu.store');
    Route::get('/{customMenu}/edit', 'edit')->name('admin.custom-menu.edit');
    Route::put('/{customMenu}', 'update')->name('admin.custom-menu.update');
    Route::delete('/{customMenu}', 'destroy')->name('admin.custom-menu.destroy');
    
    // Operations
    Route::post('/{customMenu}/toggle-status', 'toggleStatus')->name('admin.custom-menu.toggle-status');
    Route::get('/{customMenu}/assign', 'assign')->name('admin.custom-menu.assign');
    Route::post('/{customMenu}/assign-siswa', 'assignSiswa')->name('admin.custom-menu.assign-siswa');
    Route::post('/{customMenu}/remove-siswa', 'removeSiswa')->name('admin.custom-menu.remove-siswa');
    Route::post('/{customMenu}/upload-excel', 'uploadExcel')->name('admin.custom-menu.upload-excel');
    Route::get('/{customMenu}/template', 'downloadTemplate')->name('admin.custom-menu.template');
});
```

### Siswa Routes (3 routes)
```php
Route::prefix('siswa')->middleware(['auth'])->group(function () {
    Route::get('/menu', 'index')->name('siswa.menu.index');
    Route::get('/menu/{slug}', 'show')->name('siswa.menu.show');
    Route::post('/menu/{id}/mark-read', 'markAsRead')->name('siswa.menu.mark-read');
});
```

## 🗄️ Database Schema

### custom_menus Table
```sql
- id (UUID, PK)
- judul (string)
- slug (string, unique)
- icon (string, nullable)
- menu_group (enum: akademik, administrasi, hotspot, lainnya)
- content_type (enum: general, personal)
- konten (text)
- custom_fields (JSON, nullable) # Untuk personal type
- urutan (integer, default 0)
- is_active (boolean, default true)
- created_by (UUID, FK to users)
- timestamps
```

### custom_menu_siswa Table
```sql
- id (UUID, PK)
- custom_menu_id (UUID, FK cascade)
- siswa_id (UUID, FK cascade)
- personal_data (JSON, nullable)
- is_read (boolean, default false)
- read_at (datetime, nullable)
- timestamps
```

## 🔧 Key Functions & Methods

### CustomMenuController (Admin)

#### CRUD Operations
```php
index()              # DataTables with filters (group, status)
create()             # Show create form
store()              # Save new menu with unique slug generator
edit($customMenu)    # Show edit form
update($customMenu)  # Update menu
destroy($customMenu) # Delete menu (cascade delete assignments)
```

#### Menu Operations
```php
toggleStatus($customMenu)                # Toggle is_active
assign($customMenu)                      # Show assign form with 4 tabs
assignSiswa(Request $request, $menu)     # Manual/by-kelas assignment
uploadExcel(Request $request, $menu)     # Bulk import from Excel
downloadTemplate($customMenu)            # Generate Excel template
removeSiswa(Request $request, $menu)     # Remove siswa from assignment
```

### CustomMenuController (Siswa)
```php
index()                 # Show grouped menus with badge
show($slug)             # Show menu detail + mark as read
markAsRead($id)         # AJAX mark as read
getUnreadCount()        # Get unread count for notification
```

### CustomMenuImportService
```php
import($file, $customMenu)       # Parse Excel and create assignments
downloadTemplate($customMenu)    # Generate Excel template with custom columns
```

### Model Methods

#### CustomMenu
```php
toggleStatus()                              # Toggle active status
assignSiswa($siswaIds, $personalData)       # Assign siswa dengan personal data
removeSiswa($siswaIds)                      # Remove siswa dari assignment
getCustomFieldsArray()                      # Parse JSON custom_fields
getContentTypeLabel()                       # Get human-readable content type
getGroupBadgeColor()                        # Get badge color for group
```

#### CustomMenuSiswa
```php
markAsRead()                    # Mark menu as read + set read_at
getDecryptedData($key)          # Get decrypted personal data value
```

## 🐛 Bug Fixes History (15 Total)

### JavaScript/Frontend Issues
1. **Select2 not loading** - Added `@section('plugins.Select2', true)`
2. **SweetAlert2 not loading** - Added `@section('plugins.Sweetalert2', true)` + fallback CDN
3. **TinyMCE not rendering** - Changed CDN from no-api-key to jsdelivr
4. **Textarea not resizable** - Added CSS `resize: both !important`
5. **SweetAlert2 v8 compatibility** - Changed `icon` to `type`, `isConfirmed` to `value`
6. **Delete menu not working** - Changed from DELETE to POST with `_method` spoofing
7. **Badge "NEW" tidak hilang** - Added JavaScript to remove badge after menu opened

### Backend Issues
8. **Duplicate slug error** - Implemented unique slug generator with counter
9. **json_decode() error** - Fixed: `personal_data` already cast to array, removed json_decode
10. **Delete siswa from assignment** - Changed from `detach()` to `CustomMenuSiswa::where()->delete()`
11. **DataTables DT_RowIndex error** - Added `addIndexColumn()` in controller
12. **Null siswa in assign view** - Added `@if($menuSiswa->siswa)` null check
13. **Kelas relasi error** - Removed `.kelas` accessor, `kelasAktif` already returns Kelas model
14. **Template download error** - Fixed to use `getCustomFieldsArray()` method
15. **Siswa assigned count** - Added `siswa_assigned_count` to assign view

## 🎨 Frontend Libraries & Plugins

### Included via AdminLTE Plugin System
- **Select2** - Dropdown dengan search
- **SweetAlert2 v8** - Confirmation dialogs
- **DataTables** - Table dengan pagination dan search

### External CDN
- **TinyMCE 6** - `https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js`
- **PhpSpreadsheet** - Excel import/export (Composer)

### Custom JavaScript Features
- Dynamic custom fields builder
- Badge removal after menu read
- Image preview dengan zoom (pure JS, no library)

## 🚀 Setup Instructions

### Clone & Install
```bash
git clone https://github.com/chandra35/simansav3.git
cd simansav3
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
```

### Database Setup
```bash
# Configure .env database settings
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simansav3
DB_USERNAME=root
DB_PASSWORD=

# Run migrations
php artisan migrate
```

### Run Development Server
```bash
php artisan serve
```

## 📝 Panduan Melanjutkan Development di Sesi Baru

### Prompt untuk Chat Agent Baru

Ketika membuka sesi chat agent baru, berikan instruksi lengkap ini:

```
Saya ingin melanjutkan development project Laravel SIMANSA v3 yang sudah ada.

Project info:
- Path: D:\projek\simansav3 (atau sesuaikan dengan lokasi Anda)
- Laravel 11 dengan AdminLTE 3
- Database: MySQL dengan UUID primary keys
- Repository: github.com/chandra35/simansav3

Baca file DEVELOPMENT_GUIDE.md di root project untuk konteks lengkap tentang:
1. Custom Menu System yang baru selesai diimplementasi
2. Struktur file dan database
3. Bug fixes yang sudah dilakukan
4. Routes dan methods yang tersedia

Tolong baca dokumentasi tersebut dan siap untuk melanjutkan development.
```

### Tips untuk Instruksi yang Efektif

#### ❌ Instruksi Kurang Jelas
```
"Fix error di custom menu"
```

#### ✅ Instruksi yang Baik
```
Di project SIMANSA v3 (D:\projek\simansav3), fitur Custom Menu System sudah ada.
Saat akses /admin/custom-menu/create, TinyMCE tidak load.
File: resources/views/admin/custom-menu/create.blade.php
Error: TinyMCE is not defined
Tolong bantu cek dan perbaiki.
```

### Command untuk Cek Context Project

```bash
# Lihat commit terakhir dengan detail
git log -1 --stat

# Lihat file yang berubah di commit terakhir
git diff HEAD~1 --name-only

# List semua routes custom menu
php artisan route:list --path=custom-menu

# List semua routes siswa menu
php artisan route:list --path=siswa/menu

# Check migration status
php artisan migrate:status

# Check model relationships
php artisan tinker
>>> App\Models\CustomMenu::first()->siswaAssigned
>>> App\Models\CustomMenu::first()->menuSiswa
```

## 🧪 Testing Checklist

### Admin Features
- [ ] Create menu dengan content type general
- [ ] Create menu dengan content type personal + custom fields
- [ ] Edit menu dan update data
- [ ] Toggle enable/disable menu
- [ ] Delete menu (check cascade delete ke assignments)
- [ ] Download Excel template
- [ ] Upload Excel untuk assign siswa
- [ ] Manual assign siswa
- [ ] Assign by kelas
- [ ] Remove siswa from assignment
- [ ] Search siswa di DataTables assigned list

### Siswa Features
- [ ] Login sebagai siswa
- [ ] Lihat menu di sidebar (grouped by category)
- [ ] Badge "NEW" muncul untuk menu belum dibaca
- [ ] Klik menu → badge hilang
- [ ] View menu dengan content type general
- [ ] View menu dengan content type personal (personal data tampil)
- [ ] Copy button untuk personal data fields
- [ ] Show/hide password toggle
- [ ] Menu yang di-disable tidak muncul di sidebar

### Edge Cases
- [ ] Assign siswa ke menu yang sudah di-assign (handle duplicate)
- [ ] Upload Excel dengan NISN tidak ditemukan
- [ ] Upload Excel dengan format salah
- [ ] Create menu dengan slug duplicate (auto generate unique)
- [ ] Delete siswa yang sudah di-assign ke menu (check cascade)
- [ ] Logout-login siswa → badge status tetap correct

## 📞 Common Issues & Solutions

### Issue: TinyMCE tidak load
**Solution:** 
```blade
<!-- Pastikan CDN benar -->
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>

<!-- Cek selector -->
tinymce.init({
    selector: 'textarea#konten',
    // ... config
});
```

### Issue: SweetAlert tidak defined
**Solution:**
```blade
@section('plugins.Sweetalert2', true)

<!-- Atau tambahkan fallback -->
<script>
if (typeof Swal === 'undefined') {
    document.write('<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"><\/script>');
}
</script>
```

### Issue: Badge "NEW" tidak hilang
**Solution:**
```javascript
// Di view show, tambahkan:
const currentUrl = window.location.href;
$('aside .sidebar a[href="' + currentUrl + '"]').find('.badge').fadeOut(300, function() {
    $(this).remove();
});
```

### Issue: DataTables error DT_RowIndex
**Solution:**
```php
// Di controller index method
return datatables()->of($menus)
    ->addIndexColumn() // Tambahkan ini
    ->addColumn('action', function ($menu) {
        // ...
    })
    ->make(true);
```

## 📚 Additional Resources

### Laravel Documentation
- [Laravel 11 Docs](https://laravel.com/docs/11.x)
- [Eloquent Relationships](https://laravel.com/docs/11.x/eloquent-relationships)
- [Migrations](https://laravel.com/docs/11.x/migrations)

### AdminLTE Documentation
- [AdminLTE 3 Docs](https://adminlte.io/docs/3.0/)
- [Laravel AdminLTE Package](https://github.com/jeroennoten/Laravel-AdminLTE)

### Libraries Used
- [TinyMCE](https://www.tiny.cloud/docs/)
- [DataTables](https://datatables.net/manual/)
- [SweetAlert2](https://sweetalert2.github.io/)
- [Select2](https://select2.org/)
- [PhpSpreadsheet](https://phpspreadsheet.readthedocs.io/)

## 🤝 Contributing

Untuk menambahkan fitur baru atau fix bug:

1. Buat branch baru dari `main`
```bash
git checkout -b feature/nama-fitur
```

2. Lakukan perubahan dan commit dengan message yang jelas
```bash
git add .
git commit -m "feat: deskripsi fitur"
# atau
git commit -m "fix: deskripsi bug fix"
```

3. Push ke GitHub
```bash
git push origin feature/nama-fitur
```

4. Buat Pull Request di GitHub

## 📌 Notes

- Selalu run `php artisan migrate` setelah pull code baru
- Jika ada perubahan di `.env.example`, update `.env` lokal
- Gunakan `php artisan route:clear` jika routes tidak ter-update
- Untuk production, jangan lupa run `php artisan config:cache`

---

**Last Updated:** October 31, 2025  
**Version:** 1.0  
**Maintainer:** Chandra35
