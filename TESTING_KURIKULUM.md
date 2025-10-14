# Testing Guide - Kurikulum CRUD dengan Jurusan Management

## ✅ Status Implementasi
- **KurikulumController**: 11 methods dengan RBAC middleware
- **Routes**: 12 routes (7 resource + 5 custom)
- **Views**: 4 files (index, create, edit, show)
- **Jurusan Management**: Inline CRUD dengan modal (3 routes)

## 🧪 Testing Checklist

### 1. Setup & Login
```bash
# Pastikan server running
php artisan serve

# Login sebagai superadmin
Email: superadmin@example.com
Password: password
```

### 2. Test List Kurikulum (Index)
**URL**: `http://127.0.0.1:8000/admin/kurikulum`

**Expected Results**:
- ✅ Tabel dengan DataTables loading data kurikulum
- ✅ Kolom: No, Kode, Nama Kurikulum, Tahun Berlaku, Peminatan/Jurusan (badge dengan count), Status (badge aktif/non-aktif), Aksi
- ✅ Button "Tambah Kurikulum" muncul (jika ada permission `create-kurikulum`)
- ✅ Action buttons per row:
  - View (selalu muncul)
  - Edit (jika permission `edit-kurikulum`)
  - Activate/Deactivate (jika permission `activate-kurikulum`)
  - Delete (hanya jika NOT active dan ada permission `delete-kurikulum`)
- ✅ Search, pagination, dan sorting berfungsi

**Test Actions**:
- Coba search berdasarkan nama/kode kurikulum
- Klik column header untuk sorting
- Ubah entries per page (10, 25, 50, 100)

---

### 3. Test Create Kurikulum
**URL**: Click "Tambah Kurikulum" di index

**Expected Results**:
- ✅ Form dengan 5 fields:
  1. **Kode** - Text input (auto uppercase), required
  2. **Nama Kurikulum** - Text input, required
  3. **Tahun Berlaku** - Number (1990-2100), required, default 2013
  4. **Peminatan/Jurusan** - Select (Ya=1/Tidak=0), required
  5. **Deskripsi** - Textarea, optional
- ✅ Helper text dengan icon info
- ✅ Back, Reset, dan Save button
- ✅ Validation error feedback (@error directives)

**Test Cases**:
```
Test 1: Submit dengan data lengkap
- Kode: MERDEKA
- Nama: Kurikulum Merdeka Belajar
- Tahun: 2022
- Has Jurusan: 0 (Tidak)
- Deskripsi: Kurikulum berbasis kompetensi
Expected: Success, redirect ke index dengan alert "Berhasil menambah kurikulum"

Test 2: Submit dengan kode duplikat
- Kode: K13 (sudah ada)
Expected: Validation error "Kode sudah digunakan"

Test 3: Submit dengan tahun invalid
- Tahun: 1900 (kurang dari 1990)
Expected: Validation error "Tahun harus antara 1990-2100"

Test 4: Reset form
Expected: Semua field kembali ke nilai default/kosong
```

---

### 4. Test Show Kurikulum (Detail)
**URL**: Click button "View" di index

**Expected Results**:
- ✅ Widget header dengan info kurikulum (nama, kode, tahun berlaku, status badge)
- ✅ 3 statistik boxes:
  - Total Jurusan (biru)
  - Total Tahun Pelajaran (hijau)
  - Total Kelas (kuning)
- ✅ Card "Daftar Peminatan/Jurusan" (jika has_jurusan = true):
  - Tabel jurusan dengan kolom: No, Kode, Nama, Singkatan, Deskripsi, Status, Aksi
  - Button "Tambah Jurusan" di header (jika permission `manage-jurusan`)
  - Action buttons: Edit (warning), Delete (danger)
  - Alert info jika belum ada jurusan
- ✅ Card "Tahun Pelajaran Menggunakan Kurikulum Ini":
  - Tabel tahun pelajaran sorted by tahun_mulai DESC
  - Badge "Aktif" untuk tahun pelajaran yang aktif
- ✅ Action buttons:
  - Kembali (secondary)
  - Edit (primary, jika permission)
  - Aktifkan/Nonaktifkan (success/warning, jika permission)

**Test Cases**:
```
Test 1: View kurikulum dengan jurusan
- Expected: Card jurusan muncul dengan tabel berisi data jurusan

Test 2: View kurikulum tanpa jurusan (has_jurusan = false)
- Expected: Card jurusan TIDAK muncul

Test 3: Click Edit button
- Expected: Redirect ke form edit dengan data pre-filled

Test 4: Click Aktifkan (jika non-aktif)
- Expected: SweetAlert2 konfirmasi → Success → Reload → Status badge berubah "Aktif"

Test 5: Click Nonaktifkan (jika aktif)
- Expected: SweetAlert2 warning → Success → Reload → Status badge berubah "Non-Aktif"
```

---

### 5. Test Jurusan Management (Inline CRUD di Show)

#### 5.1 Add Jurusan
**Trigger**: Click "Tambah Jurusan" button

**Expected Results**:
- ✅ Modal muncul dengan title "Tambah Jurusan" (bg-primary)
- ✅ Form dengan 6 fields:
  1. Kode Jurusan - required, max 20, auto uppercase
  2. Nama Jurusan - required, max 100
  3. Singkatan - required, max 10
  4. Urutan - number, required, min 1, default 1
  5. Deskripsi - textarea, optional
  6. Status - select (Aktif/Non-Aktif), default Aktif

**Test Cases**:
```
Test 1: Submit data valid
- Kode: IPA
- Nama: Ilmu Pengetahuan Alam
- Singkatan: IPA
- Urutan: 1
- Status: Aktif
Expected: Modal close → SweetAlert2 success → Reload → Jurusan baru muncul di tabel

Test 2: Submit kode duplikat (unique: kurikulum_id + kode_jurusan)
Expected: Validation error "Kode jurusan sudah ada untuk kurikulum ini"

Test 3: Cancel/Close modal
Expected: Modal close tanpa submit
```

#### 5.2 Edit Jurusan
**Trigger**: Click button Edit (warning) pada row jurusan

**Expected Results**:
- ✅ Modal muncul dengan title "Edit Jurusan" (bg-warning)
- ✅ Form pre-filled dengan data jurusan yang dipilih
- ✅ Hidden input untuk jurusan_id

**Test Cases**:
```
Test 1: Update data jurusan
- Ubah Nama: "Ilmu Pengetahuan Sosial"
- Ubah Status: Non-Aktif
Expected: Modal close → SweetAlert2 success → Reload → Data terupdate di tabel

Test 2: Update urutan
- Ubah Urutan: 3
Expected: Success → Urutan jurusan berubah saat reload
```

#### 5.3 Delete Jurusan
**Trigger**: Click button Delete (danger) pada row jurusan

**Expected Results**:
- ✅ SweetAlert2 konfirmasi warning "Hapus jurusan ini? Data yang terkait akan terpengaruh!"

**Test Cases**:
```
Test 1: Delete jurusan yang tidak memiliki kelas
Expected: Confirm → Success → Reload → Jurusan hilang dari tabel

Test 2: Delete jurusan yang memiliki kelas
Expected: Error "Tidak dapat menghapus jurusan yang masih memiliki kelas"

Test 3: Cancel delete
Expected: SweetAlert2 close tanpa action
```

---

### 6. Test Edit Kurikulum
**URL**: Click button "Edit" di show/index

**Expected Results**:
- ✅ Form sama seperti create tapi dengan data pre-filled
- ✅ Callout info box menampilkan status badge saat ini
- ✅ Method PUT ke route `admin.kurikulum.update`
- ✅ old() helper berfungsi (preserve input saat validation error)
- ✅ Button: Kembali, Update (tidak ada reset)

**Test Cases**:
```
Test 1: Update nama kurikulum
- Ubah Nama: "Kurikulum 2013 Revisi 2018"
Expected: Success → Redirect ke show → Alert "Berhasil mengubah kurikulum"

Test 2: Update has_jurusan dari Ya ke Tidak
Expected: Validation atau business logic mencegah jika sudah ada jurusan

Test 3: Validation error
- Kosongkan Nama Kurikulum
Expected: Error "Nama kurikulum harus diisi" → old() values preserved
```

---

### 7. Test Delete Kurikulum
**Trigger**: Click button Delete di index

**Expected Results**:
- ✅ SweetAlert2 konfirmasi danger "Hapus kurikulum ini?"

**Test Cases**:
```
Test 1: Delete kurikulum non-aktif tanpa tahun pelajaran
Expected: Confirm → Success → Reload → Kurikulum hilang dari tabel

Test 2: Delete kurikulum aktif
Expected: Error "Tidak dapat menghapus kurikulum yang sedang aktif"

Test 3: Delete kurikulum yang memiliki tahun pelajaran
Expected: Error "Tidak dapat menghapus kurikulum yang sudah digunakan"
```

---

### 8. Test DataTables AJAX
**Location**: Index page

**Expected Results**:
- ✅ Server-side processing berfungsi
- ✅ Loading indicator saat fetch data
- ✅ Responsive design (collapse columns di mobile)
- ✅ Bahasa Indonesia untuk UI (search, pagination, info)

**Test Cases**:
```
Test 1: Search functionality
- Ketik "2013" di search box
Expected: Filter kurikulum yang mengandung "2013"

Test 2: Sort by Tahun Berlaku
- Click column header "Tahun Berlaku"
Expected: Data sorted DESC → Click lagi → ASC

Test 3: Change page size
- Select "50" di entries dropdown
Expected: Tabel menampilkan 50 rows per page
```

---

### 9. Test RBAC Permissions

**Setup**: Login dengan role berbeda untuk test permission

#### Test dengan role "WAKA" (38 permissions)
```
✅ Should SEE:
- Kurikulum menu di sidebar
- List kurikulum di index
- Detail kurikulum di show
- Button Edit (edit-kurikulum)
- Button Activate/Deactivate (activate-kurikulum)
- Button Tambah Jurusan (manage-jurusan)

❌ Should NOT SEE:
- Button Delete (tidak punya delete-kurikulum)
```

#### Test dengan role "Operator" (24 permissions)
```
✅ Should SEE:
- Kurikulum menu
- List kurikulum
- Detail kurikulum
- Button Tambah Kurikulum (create-kurikulum)
- Button Edit (edit-kurikulum)

❌ Should NOT SEE:
- Button Activate/Deactivate (tidak punya activate-kurikulum)
- Button Delete (tidak punya delete-kurikulum)
- Button Tambah Jurusan (tidak punya manage-jurusan)
```

#### Test dengan role "Guru" (11 permissions)
```
✅ Should SEE:
- Kurikulum menu (view-kurikulum)
- List kurikulum (read only)

❌ Should NOT SEE:
- Button Tambah Kurikulum
- Button Edit, Delete, Activate
- Button Tambah Jurusan
```

**Cara Test**:
```bash
# Buat user dengan role berbeda
php artisan tinker
> $user = User::find(2); // Ganti ID sesuai user
> $user->syncRoles('WAKA'); // Atau 'Operator', 'Guru'
> exit
```

---

### 10. Test Business Logic

**Test 1: Tidak bisa delete kurikulum aktif**
```
1. Buka detail kurikulum aktif
2. Click button Aktifkan (jika belum aktif)
3. Kembali ke index
4. Click button Delete
Expected: Error "Tidak dapat menghapus kurikulum yang sedang aktif"
```

**Test 2: Tidak bisa delete kurikulum dengan tahun pelajaran**
```
1. Buka detail kurikulum yang memiliki tahun pelajaran
2. Kembali ke index
3. Click button Delete
Expected: Error "Tidak dapat menghapus kurikulum yang sudah digunakan di tahun pelajaran"
```

**Test 3: Activate kurikulum**
```
1. Nonaktifkan semua kurikulum terlebih dahulu
2. Aktifkan kurikulum "K13"
3. Aktifkan kurikulum "MERDEKA"
Expected: Hanya "MERDEKA" yang aktif, "K13" otomatis non-aktif
Note: Business logic belum implemented (TODO)
```

---

## 🐛 Common Issues & Solutions

### Issue 1: Route tidak ditemukan
**Error**: `Route [admin.kurikulum.index] not defined`
**Solution**: 
```bash
php artisan route:clear
php artisan route:cache
php artisan route:list --name=kurikulum
```

### Issue 2: Permission denied
**Error**: 403 Forbidden atau button tidak muncul
**Solution**: 
- Cek user memiliki role yang tepat
- Cek role memiliki permission yang diperlukan
```php
// Di tinker
$user = auth()->user();
$user->roles; // Cek roles
$user->permissions; // Cek direct permissions
$user->getAllPermissions(); // Cek all (role + direct)
```

### Issue 3: DataTables tidak load data
**Error**: Console error atau tabel kosong
**Solution**:
- Cek Network tab di browser DevTools
- Pastikan endpoint `/admin/kurikulum` return JSON saat parameter `draw` ada
- Cek console untuk error JavaScript

### Issue 4: Modal tidak muncul
**Error**: Click button tapi modal tidak terbuka
**Solution**:
- Pastikan jQuery dan Bootstrap JS loaded
- Cek console untuk error
- Pastikan modal ID unique dan match dengan data-target

### Issue 5: AJAX error 419 (CSRF token mismatch)
**Solution**:
```javascript
// Pastikan CSRF token ada di head layout
<meta name="csrf-token" content="{{ csrf_token() }}">

// Dan setup AJAX headers
$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});
```

---

## 📊 Test Results Template

Copy template ini untuk dokumentasi hasil testing:

```markdown
## Test Results - Kurikulum CRUD

**Tester**: [Nama]
**Date**: [Tanggal]
**Environment**: Development / Production

| No | Test Case | Status | Notes |
|----|-----------|--------|-------|
| 1  | List Kurikulum (Index) | ✅ / ❌ | |
| 2  | Create Kurikulum | ✅ / ❌ | |
| 3  | Show Kurikulum | ✅ / ❌ | |
| 4  | Edit Kurikulum | ✅ / ❌ | |
| 5  | Delete Kurikulum | ✅ / ❌ | |
| 6  | Activate/Deactivate | ✅ / ❌ | |
| 7  | Add Jurusan | ✅ / ❌ | |
| 8  | Edit Jurusan | ✅ / ❌ | |
| 9  | Delete Jurusan | ✅ / ❌ | |
| 10 | DataTables AJAX | ✅ / ❌ | |
| 11 | RBAC Permissions | ✅ / ❌ | |
| 12 | Business Logic | ✅ / ❌ | |

**Overall Status**: ✅ All Pass / ⚠️ Some Issues / ❌ Critical Errors

**Issues Found**:
1. [Deskripsi issue]
2. [Deskripsi issue]

**Recommendations**:
1. [Saran perbaikan]
2. [Saran enhancement]
```

---

## 🚀 Next Steps

Setelah testing Kurikulum CRUD selesai, lanjut ke:

1. **KelasController** - CRUD kelas dengan fitur:
   - Filter by tahun pelajaran, tingkat, jurusan
   - Assign siswa ke kelas
   - Capacity validation
   - Assign wali kelas
   - Detail siswa per kelas

2. **MutasiSiswaController** - CRUD mutasi dengan:
   - Approval workflow
   - Reject workflow
   - Upload dokumen surat mutasi
   - Filter by status/jenis
   - DataTables server-side

3. **Dashboard Widgets** - Statistik akademik:
   - Total siswa per tingkat
   - Grafik pertumbuhan siswa
   - Status mutasi pending
   - Quick actions
