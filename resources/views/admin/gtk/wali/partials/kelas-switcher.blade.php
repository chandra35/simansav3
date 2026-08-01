{{-- Pemilih rombel bila wali kelas mengampu lebih dari satu kelas. --}}
<div class="card simansa-filter-panel mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route($route) }}" class="form-inline">
            @foreach(($extraQuery ?? []) as $qk => $qv)
                <input type="hidden" name="{{ $qk }}" value="{{ $qv }}">
            @endforeach
            <label class="mr-2 mb-0 font-weight-600"><i class="fas fa-chalkboard mr-1"></i> Rombel:</label>
            <select name="kelas_id" class="form-control mr-2" onchange="this.form.submit()">
                @foreach($kelasList as $k)
                    <option value="{{ $k->id }}" {{ $k->id === $kelas->id ? 'selected' : '' }}>
                        {{ $k->nama_lengkap ?? $k->nama_kelas }}
                    </option>
                @endforeach
            </select>
            <noscript><button type="submit" class="btn btn-primary">Pilih</button></noscript>
        </form>
    </div>
</div>
