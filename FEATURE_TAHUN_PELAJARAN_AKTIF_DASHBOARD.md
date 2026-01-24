# Feature: Tahun Pelajaran Aktif di Dashboard

**Tanggal**: 13 Oktober 2025  
**Status**: ✅ SELESAI

## Deskripsi

Menambahkan informasi tahun pelajaran aktif di bagian header dashboard (admin dan siswa) untuk memberikan konteks yang jelas tentang tahun pelajaran yang sedang aktif.

## Perubahan

### 1. Admin Dashboard Controller
**File**: `app/Http/Controllers/Admin/DashboardController.php`

**Perubahan**:
- Import `TahunPelajaran` model
- Query tahun pelajaran aktif: `TahunPelajaran::where('is_active', true)->first()`
- Pass ke view: `compact('stats', 'tahunPelajaranAktif')`

```php
use App\Models\TahunPelajaran;

public function index()
{
    // Get tahun pelajaran aktif
    $tahunPelajaranAktif = TahunPelajaran::where('is_active', true)->first();
    
    // ... stats ...
    
    return view('admin.dashboard', compact('stats', 'tahunPelajaranAktif'));
}
```

### 2. Admin Dashboard View
**File**: `resources/views/admin/dashboard.blade.php`

**Perubahan**:
- Tambahkan info tahun pelajaran di bawah header
- Style: text muted, font kecil (0.85rem)
- Icon: calendar-alt

```blade
@section('content_header')
    <h1>Dashboard Super Admin</h1>
    @if($tahunPelajaranAktif)
    <p class="text-muted" style="font-size: 0.85rem; margin-top: 5px;">
        <i class="fas fa-calendar-alt"></i> Tahun Pelajaran Aktif: 
        <strong>{{ $tahunPelajaranAktif->nama }}</strong> 
        ({{ $tahunPelajaranAktif->semester }})
    </p>
    @endif
@stop
```

### 3. Siswa Dashboard Controller
**File**: `app/Http/Controllers/Siswa/DashboardController.php`

**Perubahan**:
- Import `TahunPelajaran` model
- Query tahun pelajaran aktif
- Pass ke view: `compact('siswa', 'tahunPelajaranAktif')`

```php
use App\Models\TahunPelajaran;

public function index()
{
    // ... existing code ...
    
    // Get tahun pelajaran aktif
    $tahunPelajaranAktif = TahunPelajaran::where('is_active', true)->first();

    return view('siswa.dashboard', compact('siswa', 'tahunPelajaranAktif'));
}
```

### 4. Siswa Dashboard View
**File**: `resources/views/siswa/dashboard.blade.php`

**Perubahan**:
- Tambahkan info tahun pelajaran di bawah header
- Style sama dengan admin dashboard

```blade
@section('content_header')
    <h1><i class="fas fa-home"></i> Dashboard Siswa</h1>
    @if($tahunPelajaranAktif)
    <p class="text-muted" style="font-size: 0.85rem; margin-top: 5px;">
        <i class="fas fa-calendar-alt"></i> Tahun Pelajaran: 
        <strong>{{ $tahunPelajaranAktif->nama }}</strong> 
        ({{ $tahunPelajaranAktif->semester }})
    </p>
    @endif
@stop
```

## Tampilan

### Admin Dashboard
```
Dashboard Super Admin
📅 Tahun Pelajaran Aktif: 2024/2025 (Ganjil)
```

### Siswa Dashboard
```
🏠 Dashboard Siswa
📅 Tahun Pelajaran: 2024/2025 (Ganjil)
```

## Styling Details

- **Font Size**: 0.85rem (lebih kecil dari header)
- **Color**: text-muted (abu-abu, tidak mencolok)
- **Spacing**: margin-top: 5px (jarak dari header)
- **Icon**: fas fa-calendar-alt
- **Weight**: nama tahun pelajaran di-bold

## Conditional Display

Informasi hanya ditampilkan jika:
- Ada tahun pelajaran yang aktif (`is_active = true`)
- Menggunakan `@if($tahunPelajaranAktif)` untuk defensive check

## Benefits

✅ **Clarity**: User tahu tahun pelajaran mana yang sedang aktif  
✅ **Context**: Memberikan konteks untuk semua data yang ditampilkan  
✅ **Consistency**: Tampil di semua dashboard (admin & siswa)  
✅ **Non-intrusive**: Tulisan kecil, tidak menganggu visual  
✅ **Professional**: Tampilan yang rapi dan informatif  

## Testing Checklist

- [ ] Akses `/admin/dashboard` - tahun pelajaran aktif tampil
- [ ] Akses `/siswa/dashboard` - tahun pelajaran aktif tampil
- [ ] Set tahun pelajaran non-aktif - info tidak tampil
- [ ] Set tahun pelajaran aktif - info tampil dengan benar
- [ ] Verify styling (ukuran font, warna, spacing)

## Related Files

**Controllers**:
- `app/Http/Controllers/Admin/DashboardController.php`
- `app/Http/Controllers/Siswa/DashboardController.php`

**Views**:
- `resources/views/admin/dashboard.blade.php`
- `resources/views/siswa/dashboard.blade.php`

**Models**:
- `app/Models/TahunPelajaran.php`

## Notes

- Informasi ini menggunakan data dari tabel `tahun_pelajaran`
- Column `is_active` digunakan untuk menentukan tahun pelajaran aktif
- Hanya bisa ada 1 tahun pelajaran aktif pada satu waktu
- Semester ditampilkan dalam format: "Ganjil" atau "Genap"

---

**Dibuat oleh**: GitHub Copilot  
**Tanggal**: 13 Oktober 2025  
**Status**: ✅ COMPLETE
