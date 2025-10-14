# Fix: Kelas Model Accessor Error

**Date**: October 12, 2025  
**Issue**: `App\Models\Kelas::getNamaLengkapAttribute(): Return value must be of type string, null returned`

## Problem

The `getNamaLengkapAttribute()` accessor in the `Kelas` model was declared with a strict return type of `string`, but could return `null` when:
- `nama_kelas` field is null
- The kelas is being created but not yet fully populated

This caused errors when trying to save a new Kelas record.

## Root Cause

```php
// BEFORE (Error)
public function getNamaLengkapAttribute(): string
{
    if ($this->jurusan) {
        return "{$this->nama_kelas} ({$this->jurusan->singkatan})";
    }
    return $this->nama_kelas; // Could be NULL!
}
```

When `nama_kelas` is null, PHP throws a TypeError because the method promises to return `string` but returns `null`.

## Solution

### 1. Updated Kelas Model Accessor

Changed the return type to nullable string (`?string`) and added null check:

```php
// AFTER (Fixed)
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

**File**: `app/Models/Kelas.php`  
**Lines**: 130-139

### 2. Fixed Validation Rules

The controller had incorrect validation for `wali_kelas_id`:

```php
// BEFORE (Wrong)
'wali_kelas_id' => 'nullable|exists:users,uuid',

// AFTER (Fixed)
'wali_kelas_id' => 'nullable|exists:users,id',
```

Since `users` table uses `id` column (which is UUID), the validation should check against `id`, not `uuid`.

**Files Updated**:
- `app/Http/Controllers/Admin/KelasController.php` (store method, line 147)
- `app/Http/Controllers/Admin/KelasController.php` (update method, line 259)

## Changes Made

### 1. app/Models/Kelas.php
- Line 132: Changed return type from `string` to `?string`
- Line 134-136: Added null check for `nama_kelas`

### 2. app/Http/Controllers/Admin/KelasController.php
- Line 147 (store): Changed validation from `users,uuid` to `users,id`
- Line 259 (update): Changed validation from `users,uuid` to `users,id`

## Testing

The fix allows:
1. ✅ Creating Kelas with null `nama_kelas` temporarily (during validation phase)
2. ✅ Accessing `$kelas->nama_lengkap` returns null safely instead of throwing error
3. ✅ Proper validation of `wali_kelas_id` against UUID column
4. ✅ Normal operation when `nama_kelas` is populated

## Why This Happened

This issue emerged after the UUID conversion because:
1. The model was created with UUID support
2. The accessor was called during save process before all fields were populated
3. The strict return type caused PHP to throw TypeError
4. The validation rule was referencing wrong column name for UUID check

## Related Issues Fixed

- ✅ UUID conversion complete for all akademik tables
- ✅ Foreign keys properly using `foreignUuid`
- ✅ Validation rules now correctly reference UUID columns
- ✅ Model accessors handle null values gracefully

## Best Practice Learned

When creating model accessors (computed attributes):
1. Always consider nullable fields
2. Use nullable return types (`?string`, `?int`) when appropriate
3. Add explicit null checks before string concatenation
4. Test accessors with empty/new model instances

---

**Status**: ✅ FIXED  
**Impact**: Can now create and save Kelas records without errors  
**Breaking Changes**: None - backward compatible
