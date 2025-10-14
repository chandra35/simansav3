# Fix: "Attempt to read property 'nama' on null" Error

**Date**: October 12, 2025  
**Issue**: Error when saving Kelas: "Attempt to read property 'nama' on null"

## Problem

After fixing the accessor return type issue, a new error appeared when trying to save a Kelas record. The error occurred because:

1. Relationships were not being eagerly loaded after `Kelas::create()`
2. The `nama_lengkap` accessor tries to access the `jurusan` relationship
3. View files were accessing relationship properties without null safety checks

## Root Causes

### 1. Missing Eager Loading After Create

```php
// BEFORE (Error prone)
$kelas = Kelas::create([...]);
// Relationships not loaded, accessor fails if called
```

When Laravel creates a model and tries to serialize it (for logging, debugging, or display), any accessor that references a relationship will fail if that relationship isn't loaded.

### 2. Unsafe Property Access in Views

```blade
{{-- BEFORE (Unsafe) --}}
{{ $kelas->tahunPelajaran->nama }}
{{ $kelas->kurikulum->formatted_name }}
```

If the relationship returns null, accessing its properties throws "Attempt to read property on null" error.

## Solutions Applied

### 1. Fixed KelasController Store Method

Added eager loading immediately after creating the Kelas:

```php
// AFTER (Fixed)
$kelas = Kelas::create([...]);

// Load relationships to prevent "Attempt to read property on null" errors
$kelas->load(['tahunPelajaran', 'kurikulum', 'jurusan', 'waliKelas']);

DB::commit();
```

**File**: `app/Http/Controllers/Admin/KelasController.php`  
**Line**: 197 (added after create)

### 2. Fixed show.blade.php View

Added null coalescing operators to safely access relationship properties:

```blade
{{-- AFTER (Safe) --}}
{{ $kelas->tahunPelajaran->nama ?? '-' }}
{{ $kelas->kurikulum->formatted_name ?? '-' }}
{{ $kelas->nama_lengkap ?? $kelas->nama_kelas }}
```

**File**: `resources/views/admin/kelas/show.blade.php`  
**Lines**: 39, 43, 51

### 3. Kelas Model Accessor (Already Fixed)

The accessor now handles null values properly:

```php
public function getNamaLengkapAttribute(): ?string
{
    if (!$this->nama_kelas) {
        return null;
    }
    
    if ($this->jurusan) {
        return "{$this->nama_kelas} ({$this->jurusan->singkatan})";
    }
    return $this->nama_kelas;
}
```

## Changes Summary

### app/Http/Controllers/Admin/KelasController.php
- **Line 197**: Added `$kelas->load()` after create to eager load relationships
- **Line 147**: Fixed validation `users,uuid` → `users,id`  
- **Line 259**: Fixed validation `users,uuid` → `users,id`

### resources/views/admin/kelas/show.blade.php
- **Line 39**: Added null coalescing for `nama_lengkap`
- **Line 43**: Added null coalescing for `tahunPelajaran->nama`
- **Line 51**: Added null coalescing for `kurikulum->formatted_name`

### app/Models/Kelas.php
- **Line 132**: Changed return type `string` → `?string`
- **Line 134**: Added null check for `nama_kelas`

## Why This Happened

1. **UUID Conversion Impact**: After converting to UUID, the create flow might have changed slightly
2. **Accessor Called Too Early**: The `nama_lengkap` accessor was being called before relationships were loaded
3. **Missing Null Safety**: Views assumed relationships would always exist

## Testing Checklist

✅ Create new Kelas form loads without errors  
✅ Saving Kelas works without "Attempt to read property" error  
✅ Show page displays correctly with all relationships  
✅ DataTables listing works without errors  
✅ Edit form loads properly  
✅ Validation works correctly for all fields  

## Best Practices Applied

1. **Always eager load relationships** after creating models if accessors depend on them
2. **Use null coalescing operators** (`??`) when accessing relationship properties in views
3. **Make accessor return types nullable** when they can return null
4. **Add explicit null checks** in accessors before concatenating strings

## Related Issues Fixed

- ✅ Kelas accessor return type error
- ✅ Validation rule for UUID columns
- ✅ Null safety in views
- ✅ Eager loading after model creation

---

**Status**: ✅ FIXED  
**Impact**: Kelas CRUD operations now work correctly  
**Breaking Changes**: None - backward compatible  
**Performance**: Minimal impact - only loads relationships when needed
