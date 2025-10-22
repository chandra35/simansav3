# 📚 Fitur Tambah Siswa ke Kelas - Enhanced

## ✨ Features Added

### 🎯 **2 Cara Menambah Siswa ke Kelas:**

#### 1️⃣ **Via Select2 Dropdown** (Single/Multiple Select)
- ✅ Search siswa by Nama atau NISN
- ✅ AJAX pagination (load on scroll)
- ✅ Multiple selection (bisa pilih banyak sekaligus)
- ✅ Real-time search dengan debounce
- ✅ Tampilan info lengkap (NISN, Jenis Kelamin)
- ✅ Bootstrap 4 theme integration

**Use Case:** Tambah siswa 1-10 orang secara manual/selektif

#### 2️⃣ **Via NISN Bulk Import** (Textarea)
- ✅ Paste multiple NISN dari Excel
- ✅ Auto-cleaning: hapus karakter non-angka
- ✅ Validasi NISN (harus 10 digit)
- ✅ One NISN per line (enter-separated)
- ✅ Bulk processing dengan error handling
- ✅ Detail report: sukses/gagal per NISN

**Use Case:** Import banyak siswa sekaligus (10-50 orang) dari data EMIS

---

## 📋 Implementation Details

### **Files Modified/Created:**

#### 1. **Controller: `app/Http/Controllers/Admin/KelasController.php`**

**Method Baru:**

```php
// Get available siswa for Select2 (AJAX)
public function getAvailableSiswa(Request $request, Kelas $kelas)
```
- Return: JSON with pagination
- Query: Siswa yang belum di kelas untuk tahun pelajaran aktif
- Filter: `data_diri_completed = true`
- Search: By `nama_lengkap` atau `nisn`

```php
// Store siswa via NISN bulk
public function storeSiswaNISN(Request $request, Kelas $kelas)
```
- Input: `nisn_list` (textarea, newline-separated)
- Process: Parse → Clean → Validate → Find → Add
- Output: Success/Failed count dengan detail errors

**Logic Flow:**

```
INPUT: NISN list
  ↓
CLEAN: Hapus non-angka, trim
  ↓
VALIDATE: Must be 10 digits
  ↓
FIND: Siswa by NISN
  ↓
CHECK: 
  - Siswa exists?
  - Data diri completed?
  - Not in another kelas?
  - Capacity available?
  ↓
ADD: Attach to kelas with:
  - tahun_pelajaran_id
  - tanggal_masuk
  - status: 'aktif'
  - nomor_urut_absen (auto increment)
  ↓
OUTPUT: Success/Error report
```

#### 2. **View: `resources/views/admin/kelas/show.blade.php`**

**Added Components:**

**A. Modal with Tabs:**
```html
<div class="modal" id="modalTambahSiswa">
  <ul class="nav nav-tabs">
    <li>Pilih Siswa (Select2)</li>
    <li>Input NISN (Bulk)</li>
  </ul>
  <div class="tab-content">
    <!-- Tab 1: Select2 Form -->
    <!-- Tab 2: NISN Textarea Form -->
  </div>
</div>
```

**B. Select2 Integration:**
```javascript
$('.select2-siswa').select2({
    theme: 'bootstrap4',
    ajax: {
        url: '/admin/kelas/{id}/siswa/available',
        processResults: function(data) {
            return { results: data.items };
        }
    },
    templateResult: formatSiswa,  // Custom format
    templateSelection: formatSiswaSelection
});
```

**C. Form Handlers:**
- `#formTambahSiswaSelect` → POST to `kelas.siswa.store`
- `#formTambahSiswaNISN` → POST to `kelas.siswa.store-nisn`

#### 3. **Routes: `routes/web.php`**

**New Routes:**
```php
// AJAX endpoint for Select2
GET /admin/kelas/{kelas}/siswa/available
→ KelasController@getAvailableSiswa

// Store via NISN bulk
POST /admin/kelas/{kelas}/siswa/nisn
→ KelasController@storeSiswaNISN
```

---

## 🎨 UI/UX Features

### **Modal Design:**
- ✅ **2 Tabs:** Mudah switch antara Select2 dan NISN
- ✅ **Responsive:** Modal `modal-lg` untuk space
- ✅ **Info Banner:** Tampilkan kapasitas & sisa tempat
- ✅ **Icon Indicators:** Visual feedback untuk gender, status

### **Select2 Features:**
- ✅ **Custom Template:** Show NISN + Gender icon
- ✅ **Infinite Scroll:** Load more on scroll
- ✅ **Search Highlight:** Highlight matching text
- ✅ **Placeholder:** "Ketik nama atau NISN siswa..."
- ✅ **Multi-select:** Badge with close button

### **NISN Textarea:**
- ✅ **Large Textarea:** 10 rows untuk paste banyak
- ✅ **Placeholder:** Contoh format input
- ✅ **Helper Text:** Petunjuk penggunaan
- ✅ **Line Counter:** (via JS, optional)

### **Confirmation & Result:**
- ✅ **SweetAlert2:** Modern confirmation dialog
- ✅ **Loading State:** Show spinner during process
- ✅ **Success Toast:** Quick notification
- ✅ **Detailed Report:** Show success/failed count + errors

---

## 📊 Data Flow

### **Select2 AJAX Flow:**
```
User types → Debounce 250ms → AJAX Request
  ↓
  URL: /admin/kelas/{id}/siswa/available?q=search&page=1
  ↓
  Response: {
    items: [
      {id, text, nisn, jenis_kelamin, nama_lengkap}
    ],
    pagination: {more: true/false}
  }
  ↓
  Format & Display in dropdown
  ↓
  User selects → Form ready to submit
```

### **NISN Bulk Flow:**
```
User pastes NISN list → Form submit → Confirmation
  ↓
  Parse: Split by newline → Trim → Clean non-digit
  ↓
  Validate: Must be 10 digits → Unique → Not empty
  ↓
  Check capacity: Current + New <= Kapasitas
  ↓
  Process each NISN:
    1. Find siswa by NISN
    2. Check data_diri_completed
    3. Check not in kelas already
    4. Get next nomor_absen
    5. Attach to kelas
  ↓
  Collect results: Success array + Error array
  ↓
  Response: {
    success_count, failed_count, total, errors: [{nisn, error}]
  }
  ↓
  Display SweetAlert with detailed report
```

---

## 🔍 Validation Rules

### **Select2 Form:**
```php
'siswa_ids' => 'required|array',
'siswa_ids.*' => 'exists:siswa,uuid',
'tanggal_masuk' => 'required|date'
```

### **NISN Bulk Form:**
```php
'nisn_list' => 'required|string',
'tanggal_masuk' => 'required|date'
```

**Additional Checks:**
- ✅ NISN must be 10 digits (after cleaning)
- ✅ Siswa must exist in database
- ✅ `data_diri_completed` must be true
- ✅ Siswa not already in another kelas (same tahun pelajaran)
- ✅ Capacity check: Current + New <= Kapasitas

---

## 🧪 Testing Scenarios

### **Test Case 1: Select2 - Single Siswa**
```
1. Open detail kelas
2. Click "Tambah Siswa"
3. Tab "Pilih Siswa"
4. Type nama/NISN
5. Select 1 siswa
6. Set tanggal masuk
7. Submit
✅ Expected: 1 siswa added, page reload
```

### **Test Case 2: Select2 - Multiple Siswa**
```
1. Open modal
2. Tab "Pilih Siswa"
3. Select 5 siswa
4. Submit
✅ Expected: 5 siswa added with sequential nomor_absen
```

### **Test Case 3: NISN Bulk - Valid**
```
1. Open modal
2. Tab "Input NISN"
3. Paste 10 NISN (valid, 10 digits each)
4. Submit
✅ Expected: All 10 added successfully
```

### **Test Case 4: NISN Bulk - Mixed Valid/Invalid**
```
1. Paste 10 NISN:
   - 5 valid
   - 3 not found
   - 2 sudah di kelas lain
2. Submit
✅ Expected: 
   - Success: 5
   - Failed: 5
   - Show detail errors
```

### **Test Case 5: Capacity Check**
```
Given: Kelas capacity = 30, current = 28
1. Try add 5 siswa
✅ Expected: Error "Kapasitas tidak cukupi. Sisa: 2"
```

### **Test Case 6: Duplicate Check**
```
1. Try add siswa yang sudah di kelas ini
✅ Expected: Skip (no error, but not added twice)
```

---

## 🎯 Business Logic

### **Nomor Urut Absen:**
- Auto-increment berdasarkan siswa existing
- Query: `max(nomor_urut_absen) + 1`
- Scope: Per kelas, per tahun pelajaran

### **Siswa Availability:**
- Show only siswa yang:
  - ✅ `data_diri_completed = true`
  - ✅ Belum di kelas lain (same tahun pelajaran)
  - ✅ Status aktif

### **Capacity Management:**
- Check before add: `current + new <= kapasitas`
- Reject if exceeded
- Show sisa tempat di UI

---

## 📸 Screenshots

### **Modal - Tab Pilih Siswa (Select2):**
```
┌─────────────────────────────────────────┐
│ 🎓 Tambah Siswa ke Kelas           [X] │
├─────────────────────────────────────────┤
│ [Pilih Siswa] [Input NISN (Bulk)]     │
├─────────────────────────────────────────┤
│ ℹ️ Kapasitas: 25/30 | Sisa: 5 tempat  │
│                                         │
│ Pilih Siswa *                          │
│ ┌─────────────────────────────────────┐│
│ │ Ketik nama atau NISN siswa...      ││
│ │ [Ahmad Fauzi] [Siti Nur...] [X]    ││
│ └─────────────────────────────────────┘│
│ 💡 Ketik untuk mencari. Bisa pilih    │
│    lebih dari satu siswa               │
│                                         │
│ Tanggal Masuk *                        │
│ [2025-10-22]                           │
│                                         │
│ [✅ Tambahkan Siswa]                   │
└─────────────────────────────────────────┘
```

### **Modal - Tab Input NISN (Bulk):**
```
┌─────────────────────────────────────────┐
│ 🎓 Tambah Siswa ke Kelas           [X] │
├─────────────────────────────────────────┤
│ [Pilih Siswa] [Input NISN (Bulk)]     │
├─────────────────────────────────────────┤
│ ℹ️ Kapasitas: 25/30 | Sisa: 5 tempat  │
│                                         │
│ Daftar NISN *                          │
│ ┌─────────────────────────────────────┐│
│ │0123456789                           ││
│ │0123456790                           ││
│ │0123456791                           ││
│ │...                                  ││
│ │                                     ││
│ │                                     ││
│ │                                     ││
│ └─────────────────────────────────────┘│
│ 💡 Copy-paste dari Excel, satu NISN   │
│    per baris. NISN harus 10 digit      │
│                                         │
│ Tanggal Masuk *                        │
│ [2025-10-22]                           │
│                                         │
│ [📤 Proses Bulk Import]                │
└─────────────────────────────────────────┘
```

### **Result - Bulk Import Success:**
```
┌─────────────────────────────────────────┐
│ ⚠️ Proses Selesai!                     │
├─────────────────────────────────────────┤
│ ✅ Berhasil: 8 siswa                   │
│ ❌ Gagal: 2 NISN                       │
│ ─────────────────────────────────────  │
│ Detail Error:                          │
│ • 0123456999: NISN tidak ditemukan    │
│ • 0123456888: Siswa sudah terdaftar   │
│    di kelas lain                       │
│                                         │
│ [OK]                                   │
└─────────────────────────────────────────┘
```

---

## 🚀 Usage Guide

### **For Admin:**

#### **Cara 1: Tambah via Select2 (Recommended untuk < 10 siswa)**
1. Buka halaman **Detail Kelas**
2. Klik tombol **"Tambah Siswa"**
3. Tab **"Pilih Siswa"** (default)
4. Ketik nama atau NISN siswa
5. Pilih 1 atau lebih siswa dari dropdown
6. Set tanggal masuk
7. Klik **"Tambahkan Siswa"**
8. Konfirmasi
9. ✅ Siswa ditambahkan!

#### **Cara 2: Tambah via NISN Bulk (Recommended untuk > 10 siswa)**
1. Buka halaman **Detail Kelas**
2. Export data NISN dari EMIS (Excel)
3. Copy kolom NISN (bisa banyak sekaligus)
4. Klik tombol **"Tambah Siswa"**
5. Tab **"Input NISN (Bulk)"**
6. Paste di textarea (satu NISN per baris)
7. Set tanggal masuk
8. Klik **"Proses Bulk Import"**
9. Konfirmasi
10. ✅ Lihat report: Berhasil/Gagal

**Tips:**
- ✅ Gunakan Select2 untuk tambah siswa secara selektif
- ✅ Gunakan NISN Bulk untuk import massal dari EMIS
- ✅ NISN otomatis di-clean (hapus petik, spasi, dll)
- ✅ Cek kapasitas sebelum import banyak

---

## 🔧 Technical Notes

### **Performance:**
- Select2 AJAX: Pagination 10 items per request
- Debounce: 250ms untuk search
- Bulk: Transaction-based, rollback on failure
- Index: `nisn` column untuk fast lookup

### **Security:**
- ✅ CSRF protection
- ✅ Permission check: `assign-siswa-kelas`
- ✅ Input sanitization (NISN cleaning)
- ✅ SQL injection prevention (Eloquent)

### **Database:**
- Table: `kelas_siswa` (pivot)
- Columns: kelas_id, siswa_uuid, tahun_pelajaran_id, tanggal_masuk, status, nomor_urut_absen
- Indexes: (kelas_id, siswa_uuid, tahun_pelajaran_id)

---

## 🐛 Known Issues & Solutions

| Issue | Solution |
|-------|----------|
| Select2 tidak load data | Check route & permission |
| NISN dengan petik `'` | Auto-cleaned oleh regex |
| Duplikat NISN | Skip silent (no duplicate) |
| Capacity full | Reject dengan error message |
| Siswa sudah di kelas lain | Show error per NISN |

---

## 🔮 Future Enhancements

- [ ] Import from Excel file (upload)
- [ ] Preview before import
- [ ] Undo last import
- [ ] Export kelas roster to PDF
- [ ] WhatsApp notification to parent
- [ ] Auto-generate student card
- [ ] Bulk edit nomor absen

---

**Version:** 1.0.0  
**Date:** 2025-10-22  
**Status:** ✅ Production Ready
