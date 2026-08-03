@extends('adminlte::page')

@php
    $hasTemplate = isset($polling);
    $editing = $hasTemplate && $polling->exists;
    $selected = fn($type, $audience) => $hasTemplate ? $polling->targets->where('audience_type', $audience)->where('scope_type', $type)->pluck('scope_value')->all() : [];
    $audienceValue = old('audience', $polling->audience ?? 'siswa');
    $studentAllValue = (string) old('student_all', $hasTemplate && in_array('all', $polling->targets->where('audience_type', 'siswa')->pluck('scope_type')->all()) ? '1' : '0');
    $gtkAllValue = (string) old('gtk_all', $hasTemplate && in_array('all', $polling->targets->where('audience_type', 'gtk')->pluck('scope_type')->all()) ? '1' : '0');
    $selectedClasses = old('student_classes', $selected('kelas', 'siswa'));
    $selectedGtkCategories = old('gtk_categories', $selected('kategori_ptk', 'gtk'));
    $selectedGtks = old('gtks', $selected('gtk', 'gtk'));
    $questions = old('questions', $hasTemplate ? $polling->questions->map(fn($q) => [
        'prompt'=>$q->prompt,'type'=>$q->type,'is_required'=>$q->is_required,
        'min_selections'=>$q->min_selections,'max_selections'=>$q->max_selections,
        'options_text'=>$q->options->pluck('label')->implode("\n"),
    ])->all() : [[
        'prompt'=>'','type'=>'single','is_required'=>true,'min_selections'=>null,'max_selections'=>null,'options_text'=>"Pilihan 1\nPilihan 2",
    ]]);
@endphp

@section('title', $editing ? 'Edit Polling' : 'Buat Polling')
@section('content_header')
<div class="row mb-2"><div class="col-sm-6"><h1><i class="fas fa-edit text-primary mr-2"></i>{{ $editing ? 'Edit Polling' : 'Buat Polling' }}</h1></div><div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="{{ route('admin.polling.index') }}">Polling</a></li><li class="breadcrumb-item active">Form</li></ol></div></div>
@stop

@section('content')
<div class="simansa-polling-form">
    <form method="POST" action="{{ $editing ? route('admin.polling.update',$polling) : route('admin.polling.store') }}" id="pollingForm">
        @csrf @if($editing) @method('PUT') @endif
        @if(isset($sourcePolling))<input type="hidden" name="source_polling_id" value="{{ $sourcePolling->id }}">@endif
        <div class="card bg-gradient-primary text-white mb-4 simansa-form-hero"><div class="card-body"><div class="row align-items-center"><div class="col-lg-8"><div class="small font-weight-bold text-uppercase mb-2"><i class="fas fa-magic mr-1"></i> Builder Polling</div><h2 class="h3 font-weight-bold mb-2">Rancang respons yang terukur</h2><p class="mb-0">Atur identitas, target, pertanyaan, aturan pilihan, persetujuan, dan jadwal publikasi.</p></div><div class="col-lg-4 mt-3 mt-lg-0 text-lg-right"><div class="d-flex flex-wrap justify-content-lg-end hero-actions"><button type="button" id="previewPolling" class="btn btn-outline-light"><i class="fas fa-eye mr-1"></i> Preview</button><div class="dropdown"><button type="button" class="btn btn-light dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-layer-group mr-1"></i> Preset Cepat</button><div class="dropdown-menu dropdown-menu-right"><button type="button" id="tkaPreset" class="dropdown-item"><i class="fas fa-graduation-cap text-primary mr-2"></i>Preset TKA Kelas XII</button><button type="button" id="satisfactionPreset" class="dropdown-item"><i class="fas fa-smile text-success mr-2"></i>Survei Kepuasan</button><button type="button" id="confirmationPreset" class="dropdown-item"><i class="fas fa-check-double text-info mr-2"></i>Konfirmasi Kegiatan</button><div class="dropdown-divider"></div><a href="{{ route('admin.polling.index') }}#pollingHistory" class="dropdown-item"><i class="fas fa-history text-secondary mr-2"></i>Preset dari Riwayat</a></div></div></div></div></div></div></div>

        @if(isset($sourcePolling))<div class="alert alert-info border-0 shadow-sm"><i class="fas fa-copy mr-2"></i>Form disalin dari <strong>{{ $sourcePolling->title }}</strong> ({{ $sourcePolling->created_at->format('d/m/Y') }}, Tahun Ajaran {{ $sourcePolling->tahun_pelajaran_snapshot ?: 'belum tercatat' }}). Periksa kembali target responden aktif sebelum menyimpan.</div>@endif

        @if($errors->any())<div class="alert alert-danger"><strong>Periksa kembali form:</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card card-outline card-primary"><div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-info-circle text-primary mr-2"></i>Identitas & Jadwal</h3></div><div class="card-body">
                    <div class="form-group"><label>Nama Polling <span class="text-danger">*</span></label><input name="title" id="pollTitle" class="form-control" maxlength="255" required value="{{ old('title',$polling->title??'') }}" placeholder="Contoh: Pemilihan Mata Pelajaran TKA 2026"></div>
                    <div class="form-group"><label>Deskripsi</label><textarea name="description" id="pollDescription" class="form-control" rows="3" placeholder="Jelaskan tujuan dan petunjuk singkat polling">{{ old('description',$polling->description??'') }}</textarea></div>
                    <div class="row"><div class="col-md-6 form-group"><label>Mulai <span class="text-danger">*</span></label><input type="datetime-local" name="starts_at" class="form-control" required value="{{ old('starts_at',isset($polling)?$polling->starts_at->format('Y-m-d\TH:i'):now()->addHour()->format('Y-m-d\TH:i')) }}"></div><div class="col-md-6 form-group"><label>Selesai <span class="text-danger">*</span></label><input type="datetime-local" name="ends_at" class="form-control" required value="{{ old('ends_at',isset($polling)?$polling->ends_at->format('Y-m-d\TH:i'):now()->addDays(7)->format('Y-m-d\TH:i')) }}"></div></div>
                </div></div>

                <div class="card card-outline card-primary"><div class="card-header d-flex align-items-center"><h3 class="card-title font-weight-bold flex-grow-1"><i class="fas fa-question-circle text-primary mr-2"></i>Pertanyaan</h3><button type="button" class="btn btn-sm btn-primary" id="addQuestion"><i class="fas fa-plus mr-1"></i>Tambah Pertanyaan</button></div><div class="card-body"><div id="questionBuilder"></div></div></div>
            </div>

            <div class="col-lg-4">
                <div class="card card-outline card-primary target-card"><div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-bullseye text-primary mr-2"></i>Target Responden</h3></div><div class="card-body">
                    <label class="small font-weight-bold d-block">Jenis responden</label>
                    <div class="audience-options mb-3">
                        @foreach(['siswa'=>['fa-user-graduate','Siswa'],'gtk'=>['fa-chalkboard-teacher','GTK'],'both'=>['fa-users','Siswa & GTK']] as $key=>$option)
                            <label class="audience-option"><input type="radio" name="audience" value="{{ $key }}" @checked($audienceValue===$key)><span><i class="fas {{ $option[0] }}"></i>{{ $option[1] }}</span></label>
                        @endforeach
                    </div>

                    <div id="studentTargets" class="target-section">
                        <h6 class="font-weight-bold"><i class="fas fa-user-graduate text-primary mr-1"></i>Target Siswa</h6>
                        <div class="mode-options mb-3">
                            <div class="custom-control custom-radio custom-control-inline"><input class="custom-control-input student-mode" type="radio" id="studentAll" name="student_all" value="1" @checked($studentAllValue==='1')><label class="custom-control-label" for="studentAll">Semua</label></div>
                            <div class="custom-control custom-radio custom-control-inline"><input class="custom-control-input student-mode" type="radio" id="studentCustom" name="student_all" value="0" @checked($studentAllValue!=='1')><label class="custom-control-label" for="studentCustom">Custom</label></div>
                        </div>
                        <div id="studentCustomOptions" class="custom-target-panel">
                            <label class="small font-weight-bold">Tingkat</label>
                            <div class="d-flex flex-wrap mb-3">@foreach([10=>'X',11=>'XI',12=>'XII'] as $grade=>$roman)<div class="custom-control custom-checkbox mr-3"><input class="custom-control-input student-scope grade-check" type="checkbox" name="student_grades[]" value="{{ $grade }}" id="grade{{ $grade }}" @checked(in_array((string)$grade,array_map('strval',old('student_grades',$selected('tingkat','siswa')))))><label class="custom-control-label" for="grade{{ $grade }}">{{ $roman }}</label></div>@endforeach</div>
                            <div class="d-flex justify-content-between align-items-center mb-2"><label class="small font-weight-bold mb-0">Rombel aktif <span class="badge badge-primary ml-1" id="classSelectedCount">0</span></label><div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input" id="allClasses"><label class="custom-control-label small" for="allClasses">Pilih semua yang tampil</label></div></div>
                            <input type="search" id="classSearch" class="form-control form-control-sm mb-2" placeholder="Cari rombel...">
                            <div class="checklist-panel" id="classChecklist">
                                @forelse($classes as $class)<div class="custom-control custom-checkbox checklist-row" data-grade="{{ $class->tingkat }}" data-search="{{ strtolower($class->nama_kelas.' '.$class->tingkat) }}"><input class="custom-control-input student-scope class-check" type="checkbox" name="student_classes[]" value="{{ $class->id }}" id="class{{ $class->id }}" @checked(in_array($class->id,$selectedClasses))><label class="custom-control-label" for="class{{ $class->id }}"><strong>{{ $class->nama_kelas }}</strong><small>Tingkat {{ $class->tingkat }}</small></label></div>@empty<p class="text-muted small mb-0">Belum ada rombel aktif.</p>@endforelse
                                <p class="text-muted small mb-0 p-2" id="noClassMatches" style="display:none">Tidak ada rombel sesuai tingkat atau pencarian.</p>
                            </div>
                        </div>
                    </div>

                    <div id="gtkTargets" class="target-section border-top pt-3 mt-3">
                        <h6 class="font-weight-bold"><i class="fas fa-chalkboard-teacher text-primary mr-1"></i>Target GTK</h6>
                        <div class="mode-options mb-3">
                            <div class="custom-control custom-radio custom-control-inline"><input class="custom-control-input gtk-mode" type="radio" id="gtkAll" name="gtk_all" value="1" @checked($gtkAllValue==='1')><label class="custom-control-label" for="gtkAll">Semua</label></div>
                            <div class="custom-control custom-radio custom-control-inline"><input class="custom-control-input gtk-mode" type="radio" id="gtkCustom" name="gtk_all" value="0" @checked($gtkAllValue!=='1')><label class="custom-control-label" for="gtkCustom">Custom</label></div>
                        </div>
                        <div id="gtkCustomOptions" class="custom-target-panel">
                            <label class="small font-weight-bold d-block">Kategori</label>
                            @foreach(['Pendidik'=>'Guru','Tenaga Kependidikan'=>'Staf'] as $value=>$label)<div class="custom-control custom-checkbox custom-control-inline mb-3"><input type="checkbox" class="custom-control-input gtk-scope" name="gtk_categories[]" value="{{ $value }}" id="gtkCategory{{ $loop->index }}" @checked(in_array($value,$selectedGtkCategories))><label class="custom-control-label" for="gtkCategory{{ $loop->index }}">{{ $label }}</label></div>@endforeach
                            <button type="button" class="btn btn-sm btn-primary btn-block" data-toggle="modal" data-target="#gtkTargetModal"><i class="fas fa-user-check mr-1"></i>Pilih GTK Tertentu <span class="badge badge-light ml-1" id="gtkSelectedCount">0</span></button>
                            <small class="text-muted d-block mt-2">Kategori dan GTK individual dapat dipakai bersamaan.</small>
                        </div>
                    </div>
                </div></div>

                <div class="card card-outline card-primary"><div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-sliders-h text-primary mr-2"></i>Aturan</h3></div><div class="card-body">
                    <input type="hidden" name="allow_changes" value="0"><div class="custom-control custom-switch mb-3"><input type="checkbox" class="custom-control-input" id="allowChanges" name="allow_changes" value="1" @checked(old('allow_changes',$polling->allow_changes??true))><label class="custom-control-label" for="allowChanges">Boleh mengubah jawaban</label></div>
                    <input type="hidden" name="show_results_after_submit" value="0"><div class="custom-control custom-switch mb-3"><input type="checkbox" class="custom-control-input" id="showResults" name="show_results_after_submit" value="1" @checked(old('show_results_after_submit',$polling->show_results_after_submit??false))><label class="custom-control-label" for="showResults">Tampilkan hasil agregat</label></div>
                    <input type="hidden" name="require_consent" value="0"><div class="custom-control custom-switch mb-3"><input type="checkbox" class="custom-control-input" id="requireConsent" name="require_consent" value="1" @checked(old('require_consent',$polling->require_consent??false))><label class="custom-control-label" for="requireConsent">Wajib pernyataan persetujuan</label></div>
                    <div class="form-group" id="consentBox"><label>Teks Persetujuan</label><textarea name="consent_text" id="consentText" rows="3" class="form-control">{{ old('consent_text',$polling->consent_text??'Saya menyatakan pilihan yang saya kirim sudah benar dan dapat dipertanggungjawabkan.') }}</textarea></div>
                    <div class="form-group mb-0"><label>Jeda Pengingat</label><div class="input-group"><input type="number" name="reminder_interval_hours" min="1" max="168" class="form-control" required value="{{ old('reminder_interval_hours',$polling->reminder_interval_hours??6) }}"><div class="input-group-append"><span class="input-group-text">jam</span></div></div></div>
                </div></div>
            </div>
        </div>

        <div class="modal fade" id="pollingPreviewModal" tabindex="-1" role="dialog" aria-labelledby="pollingPreviewLabel" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-scrollable" role="document"><div class="modal-content"><div class="modal-header"><div><h5 class="modal-title font-weight-bold" id="pollingPreviewLabel"><i class="fas fa-eye text-primary mr-2"></i>Preview Tampilan Responden</h5><small class="text-muted">Simulasi ini mengikuti isian builder saat ini dan tidak menyimpan jawaban.</small></div><button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button></div><div class="modal-body bg-light"><div id="pollingPreviewContent" class="polling-preview-content"></div></div><div class="modal-footer"><span class="text-muted mr-auto"><i class="fas fa-info-circle mr-1"></i>Field sengaja dinonaktifkan dalam mode preview.</span><button type="button" class="btn btn-primary" data-dismiss="modal"><i class="fas fa-edit mr-1"></i>Kembali Mengedit</button></div></div></div></div>

        <div class="modal fade" id="gtkTargetModal" tabindex="-1" role="dialog" aria-labelledby="gtkTargetModalLabel" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable" role="document"><div class="modal-content"><div class="modal-header"><div><h5 class="modal-title font-weight-bold" id="gtkTargetModalLabel"><i class="fas fa-user-check text-primary mr-2"></i>Pilih GTK Tertentu</h5><small class="text-muted">Centang GTK yang akan menerima polling di luar pilihan kategori.</small></div><button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button></div><div class="modal-body"><div class="row align-items-center mb-3"><div class="col-md-8"><div class="input-group"><div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-search"></i></span></div><input type="search" id="gtkSearch" class="form-control" placeholder="Cari nama, NIK, ID PTK, jenis PTK..."></div></div><div class="col-md-4 mt-2 mt-md-0"><div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input" id="allGtks"><label class="custom-control-label" for="allGtks">Pilih semua yang tampil</label></div></div></div><div class="table-responsive"><table class="table table-hover table-sm mb-0 gtk-target-table"><thead><tr><th style="width:42px"></th><th>GTK</th><th>Kategori / Jenis PTK</th><th>ID PTK</th></tr></thead><tbody>@forelse($gtks as $gtk)<tr class="gtk-row" data-search="{{ strtolower($gtk->nama_lengkap.' '.$gtk->nik.' '.$gtk->peg_id.' '.$gtk->kategori_ptk.' '.$gtk->jenis_ptk) }}"><td class="align-middle"><div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input gtk-check gtk-scope" name="gtks[]" value="{{ $gtk->id }}" id="targetGtk{{ $gtk->id }}" @checked(in_array($gtk->id,$selectedGtks))><label class="custom-control-label" for="targetGtk{{ $gtk->id }}"><span class="sr-only">Pilih {{ $gtk->nama_lengkap }}</span></label></div></td><td class="align-middle"><div class="d-flex align-items-center"><img src="{{ $gtk->foto_profile_url }}" alt="" class="gtk-avatar mr-2"><div><strong class="d-block">{{ $gtk->nama_lengkap }}</strong><small class="text-muted">NIK {{ $gtk->nik ?: '-' }}</small></div></div></td><td class="align-middle">@php($categoryLabel = $gtk->kategori_ptk === 'Pendidik' ? 'Guru' : ($gtk->kategori_ptk === 'Tenaga Kependidikan' ? 'Staf' : 'Belum dikategorikan'))<span class="badge badge-{{ $gtk->kategori_ptk === 'Pendidik' ? 'primary' : ($gtk->kategori_ptk === 'Tenaga Kependidikan' ? 'info' : 'secondary') }}">{{ $categoryLabel }}</span><small class="d-block text-muted mt-1">{{ $gtk->jenis_ptk ?: '-' }}</small></td><td class="align-middle text-nowrap">{{ $gtk->peg_id ?: '-' }}</td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-4">Belum ada GTK aktif.</td></tr>@endforelse</tbody></table></div></div><div class="modal-footer"><span class="text-muted mr-auto"><strong id="gtkModalCount">0</strong> GTK dipilih</span><button type="button" class="btn btn-primary" data-dismiss="modal"><i class="fas fa-check mr-1"></i>Selesai</button></div></div></div></div>

        <div class="card"><div class="card-body d-flex flex-wrap justify-content-between"><a href="{{ route('admin.polling.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i>Kembali</a><div><button name="action" value="draft" class="btn btn-outline-primary mr-2"><i class="fas fa-save mr-1"></i>Simpan Draft</button><button name="action" value="publish" class="btn btn-primary"><i class="fas fa-paper-plane mr-1"></i>Simpan & Terbitkan</button></div></div></div>
    </form>
</div>
@stop

@section('css')
<style>
.simansa-polling-form .simansa-form-hero{border:0;border-radius:18px;overflow:visible;position:relative;z-index:20}
.simansa-polling-form .simansa-form-hero .card-body{border-radius:18px}
.simansa-polling-form .hero-actions{gap:.5rem}.simansa-polling-form .simansa-form-hero .dropdown-menu{z-index:1080}
.simansa-polling-form .question-card{border:1px solid #dbe3ef;border-left:4px solid #3b82f6;border-radius:12px;padding:1rem;margin-bottom:1rem;background:#f8fafc}
.simansa-polling-form .question-number{width:30px;height:30px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:#dbeafe;color:#1d4ed8;font-weight:800}
.simansa-polling-form label{color:#334155}
.simansa-polling-form .audience-options{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.5rem}
.simansa-polling-form .audience-option{margin:0}
.simansa-polling-form .audience-option input{position:absolute;opacity:0}
.simansa-polling-form .audience-option span{align-items:center;background:#fff;border:1px solid #cbd5e1;border-radius:8px;cursor:pointer;display:flex;flex-direction:column;font-size:.76rem;gap:.25rem;justify-content:center;min-height:62px;padding:.5rem;text-align:center}
.simansa-polling-form .audience-option input:checked+span{background:#eff6ff;border-color:#2563eb;color:#1d4ed8;font-weight:700;box-shadow:0 0 0 1px #2563eb}
.simansa-polling-form .custom-target-panel{background:#f8fafc;border:1px solid #dbe3ef;border-radius:10px;padding:.75rem}
.simansa-polling-form .checklist-panel{background:#fff;border:1px solid #dbe3ef;border-radius:8px;max-height:220px;overflow-y:auto;padding:.25rem}
.simansa-polling-form .checklist-row{border-bottom:1px solid #eef2f7;margin:0;padding:.55rem .5rem .55rem 2rem}
.simansa-polling-form .checklist-row:last-child{border-bottom:0}
.simansa-polling-form .checklist-row label{display:flex;justify-content:space-between;width:100%}
.simansa-polling-form .checklist-row label small{color:#64748b}
.simansa-polling-form .gtk-target-table{min-width:680px}
.simansa-polling-form .gtk-avatar{border-radius:50%;height:38px;object-fit:cover;width:38px}
.simansa-polling-form .polling-preview-content{max-width:980px;margin:0 auto}.simansa-polling-form .preview-hero{border:0;border-radius:16px;overflow:hidden}.simansa-polling-form .preview-deadline{background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.3);border-radius:10px;padding:.7rem 1rem}.simansa-polling-form .preview-question-index{align-items:center;background:#dbeafe;border-radius:50%;color:#1d4ed8;display:inline-flex;height:30px;justify-content:center;margin-right:.65rem;width:30px}.simansa-polling-form .preview-option-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.65rem}.simansa-polling-form .preview-option{align-items:center;background:#fff;border:1px solid #cbd5e1;border-radius:9px;display:flex;gap:.6rem;min-height:48px;padding:.7rem}.simansa-polling-form .preview-consent{background:#fffbeb;border-left:4px solid #f59e0b}
@media(max-width:575.98px){.simansa-polling-form .card-body{padding:1rem}.simansa-polling-form .card-body.d-flex{gap:.75rem}.simansa-polling-form .card-body.d-flex>div{display:flex;width:100%}.simansa-polling-form .card-body.d-flex>div .btn{flex:1}.simansa-polling-form .audience-options{grid-template-columns:1fr}.simansa-polling-form .audience-option span{flex-direction:row;min-height:44px}.simansa-polling-form .modal-body{padding:.75rem}.simansa-polling-form .hero-actions>*{flex:1}.simansa-polling-form .hero-actions .btn{width:100%}.simansa-polling-form .preview-option-grid{grid-template-columns:1fr}}
</style>
@stop

@section('js')
<script>
$(function(){
    let questionIndex=0; const seed=@json($questions);
    const esc=v=>$('<div>').text(v??'').html();
    function addQuestion(data={}){const i=questionIndex++; const type=data.type||'single'; const card=$( `
        <div class="question-card" data-index="${i}"><div class="d-flex align-items-center mb-3"><span class="question-number mr-2"></span><strong class="flex-grow-1">Pertanyaan</strong><button type="button" class="btn btn-sm btn-outline-danger remove-question"><i class="fas fa-trash"></i></button></div>
        <div class="form-group"><label>Pertanyaan <span class="text-danger">*</span></label><textarea name="questions[${i}][prompt]" class="form-control" rows="2" required>${esc(data.prompt||'')}</textarea></div>
        <div class="row"><div class="col-md-5 form-group"><label>Jenis Jawaban</label><select name="questions[${i}][type]" class="form-control question-type"><option value="single">Pilihan tunggal</option><option value="multiple">Pilihan ganda</option><option value="short_text">Teks singkat</option><option value="long_text">Teks panjang</option><option value="yes_no">Ya / Tidak</option></select></div><div class="col-md-7 form-group d-flex align-items-end"><input type="hidden" name="questions[${i}][is_required]" value="0"><div class="custom-control custom-switch mb-2"><input type="checkbox" class="custom-control-input" name="questions[${i}][is_required]" value="1" id="required${i}"><label class="custom-control-label" for="required${i}">Wajib dijawab</label></div></div></div>
        <div class="choice-fields"><div class="form-group"><label>Opsi Jawaban <small class="text-muted">(satu pilihan per baris)</small></label><textarea name="questions[${i}][options_text]" class="form-control options-text" rows="5">${esc(data.options_text||'')}</textarea></div></div>
        <div class="multiple-fields row"><div class="col-6 form-group"><label>Minimal dipilih</label><input type="number" min="1" name="questions[${i}][min_selections]" class="form-control" value="${esc(data.min_selections||'')}"></div><div class="col-6 form-group"><label>Maksimal dipilih</label><input type="number" min="1" name="questions[${i}][max_selections]" class="form-control" value="${esc(data.max_selections||'')}"></div></div></div>`);
        card.find('.question-type').val(type); card.find('[name$="[is_required]"][type=checkbox]').prop('checked',data.is_required!==false&&String(data.is_required)!=='0'); $('#questionBuilder').append(card); toggleQuestion(card); renumber();
    }
    function toggleQuestion(card){const type=card.find('.question-type').val(); card.find('.choice-fields').toggle(['single','multiple'].includes(type)); card.find('.multiple-fields').toggle(type==='multiple');}
    function renumber(){$('.question-card').each((i,e)=>$(e).find('.question-number').text(i+1)); $('.remove-question').prop('disabled',$('.question-card').length===1);}
    seed.forEach(addQuestion); $('#addQuestion').on('click',()=>addQuestion({type:'single',is_required:true,options_text:'Pilihan 1\nPilihan 2'})); $(document).on('change','.question-type',function(){toggleQuestion($(this).closest('.question-card'))}); $(document).on('click','.remove-question',function(){$(this).closest('.question-card').remove();renumber()});
    function toggleAudience(){const a=$('[name="audience"]:checked').val();$('#studentTargets').toggle(a==='siswa'||a==='both');$('#gtkTargets').toggle(a==='gtk'||a==='both')}
    function toggleTargetModes(){const studentAll=$('[name="student_all"]:checked').val()==='1';const gtkAll=$('[name="gtk_all"]:checked').val()==='1';$('#studentCustomOptions').toggle(!studentAll).find(':input').prop('disabled',studentAll);$('#gtkCustomOptions').toggle(!gtkAll).find(':input').prop('disabled',gtkAll);$('.gtk-check').prop('disabled',gtkAll)}
    function updateGtkCount(){const count=$('.gtk-check:checked').length;$('#gtkSelectedCount,#gtkModalCount').text(count)}
    $('[name="audience"]').on('change',toggleAudience);$('.student-mode,.gtk-mode').on('change',function(){toggleTargetModes();filterClasses();filterGtks()});$('#requireConsent').on('change',()=>$('#consentBox').toggle($('#requireConsent').prop('checked')));
    function filterClasses(){const grades=$('.grade-check:checked').map((_,e)=>e.value).get();const term=$('#classSearch').val().toLowerCase();let shown=0;$('#classChecklist .checklist-row').each(function(){const visible=(!grades.length||grades.includes(String($(this).data('grade'))))&&$(this).data('search').includes(term);$(this).toggle(visible);if(visible)shown++});$('#noClassMatches').toggle(shown===0);const visible=$('.class-check:visible');$('#allClasses').prop('checked',visible.length>0&&visible.filter(':not(:checked)').length===0)}
    function updateClassCount(){$('#classSelectedCount').text($('.class-check:checked').length)}
    $('#classSearch').on('input',filterClasses);$('.grade-check').on('change',filterClasses);
    $('#allClasses').on('change',function(){$('.class-check:visible').prop('checked',this.checked);updateClassCount()});$('.class-check').on('change',function(){updateClassCount();filterClasses()});
    function filterGtks(){const categories=$('[name="gtk_categories[]"]:checked').map((_,e)=>e.value).get();const term=$('#gtkSearch').val().toLowerCase();$('.gtk-row').each(function(){const badge=$(this).find('td:eq(2) .badge').text().trim();const category=badge==='Guru'?'Pendidik':(badge==='Staf'?'Tenaga Kependidikan':'');$(this).toggle($(this).data('search').includes(term)&&(!categories.length||categories.includes(category)))});$('#allGtks').prop('checked',false)}
    $('#gtkSearch').on('input',filterGtks);$('[name="gtk_categories[]"]').on('change',filterGtks);
    $('#allGtks').on('change',function(){$('.gtk-row:visible .gtk-check').prop('checked',this.checked);updateGtkCount()});$('.gtk-check').on('change',updateGtkCount);
    toggleAudience();toggleTargetModes();updateGtkCount();updateClassCount();filterClasses();filterGtks();$('#requireConsent').trigger('change');
    function renderPreview(){
        const title=esc($('#pollTitle').val().trim()||'Judul polling belum diisi');
        const description=esc($('#pollDescription').val().trim()||'Deskripsi polling akan tampil di sini.');
        const audience={siswa:'SISWA',gtk:'GTK',both:'SISWA & GTK'}[$('[name="audience"]:checked').val()]||'RESPONDEN';
        const rawDeadline=$('[name="ends_at"]').val();
        const deadline=rawDeadline?new Date(rawDeadline).toLocaleString('id-ID',{dateStyle:'short',timeStyle:'short'}).replace('.',':'):'Belum ditentukan';
        let questions='';
        $('.question-card').each(function(index){
            const card=$(this),type=card.find('.question-type').val(),prompt=esc(card.find('[name$="[prompt]"]').val().trim()||'Pertanyaan belum diisi');
            const required=card.find('[name$="[is_required]"][type="checkbox"]').prop('checked')?'<span class="text-danger">*</span>':'';
            let answer='';
            if(type==='single'||type==='multiple'||type==='yes_no'){
                const options=type==='yes_no'?['Ya','Tidak']:card.find('.options-text').val().split(/\r?\n/).map(v=>v.trim()).filter(Boolean);
                const icon=type==='multiple'?'fa-square':'fa-circle';
                const limits=type==='multiple'?`<div class="small text-muted mb-2">Pilih ${esc(card.find('[name$="[min_selections]"]').val()||'minimal satu')} sampai ${esc(card.find('[name$="[max_selections]"]').val()||'batas yang ditentukan')} opsi.</div>`:'';
                answer=limits+'<div class="preview-option-grid">'+(options.length?options:['Opsi belum diisi']).map(option=>`<div class="preview-option"><i class="far ${icon} text-primary"></i>${esc(option)}</div>`).join('')+'</div>';
            }else if(type==='short_text') answer='<input class="form-control" placeholder="Jawaban singkat responden" disabled>';
            else answer='<textarea class="form-control" rows="4" placeholder="Jawaban responden" disabled></textarea>';
            questions+=`<div class="card card-outline card-primary"><div class="card-header"><h3 class="card-title font-weight-bold"><span class="preview-question-index">${index+1}</span>${prompt} ${required}</h3></div><div class="card-body">${answer}</div></div>`;
        });
        const consent=$('#requireConsent').prop('checked')?`<div class="card preview-consent"><div class="card-body"><i class="far fa-square text-warning mr-2"></i><strong>${esc($('#consentText').val().trim()||'Pernyataan persetujuan')}</strong></div></div>`:'';
        $('#pollingPreviewContent').html(`<div class="card bg-gradient-primary text-white preview-hero"><div class="card-body"><div class="row align-items-center"><div class="col-lg-8"><div class="small text-uppercase font-weight-bold mb-2"><i class="fas fa-clipboard-check mr-1"></i>${audience} · MODE PREVIEW</div><h2 class="h3 font-weight-bold mb-2">${title}</h2><p class="mb-0">${description}</p></div><div class="col-lg-4 mt-3 mt-lg-0"><div class="preview-deadline"><small>Batas Pengisian</small><strong class="d-block">${esc(deadline)} WIB</strong></div></div></div></div></div>${questions}${consent}<div class="card mb-0"><div class="card-body d-flex justify-content-between align-items-center"><button type="button" class="btn btn-secondary" disabled><i class="fas fa-arrow-left mr-1"></i>Kembali</button><button type="button" class="btn btn-primary" disabled><i class="fas fa-paper-plane mr-1"></i>Kirim Jawaban</button></div></div>`);
    }
    $('#previewPolling').on('click',function(){renderPreview();$('#pollingPreviewModal').modal('show')});
    $('#tkaPreset').on('click',function(){Swal.fire({icon:'question',title:'Gunakan preset TKA?',text:'Identitas dan pertanyaan saat ini akan diganti dengan contoh pemilihan dua mapel TKA kelas XII.',showCancelButton:true,confirmButtonText:'Gunakan Preset',cancelButtonText:'Batal',confirmButtonColor:'#2563eb'}).then(r=>{if(!r.isConfirmed)return;$('#pollTitle').val('Pemilihan Mata Pelajaran Pilihan TKA 2026');$('#pollDescription').val('Pilih tepat dua mata pelajaran pilihan Tes Kemampuan Akademik yang tercatat di rapor serta sesuai minat dan rencana studi lanjut.');$('[name="audience"][value="siswa"]').prop('checked',true).trigger('change');$('#studentCustom').prop('checked',true).trigger('change');$('.grade-check,.class-check').prop('checked',false);$('#grade12').prop('checked',true);$('#allClasses').prop('checked',false);filterClasses();updateClassCount();$('#questionBuilder').empty();questionIndex=0;addQuestion({prompt:'Pilih tepat dua mata pelajaran pilihan TKA',type:'multiple',is_required:true,min_selections:2,max_selections:2,options_text:'Matematika Tingkat Lanjut\nBahasa Indonesia Tingkat Lanjut\nBahasa Inggris Tingkat Lanjut\nFisika\nKimia\nBiologi\nEkonomi\nSosiologi\nGeografi\nSejarah\nAntropologi\nPPKn/Pendidikan Pancasila\nBahasa Arab\nBahasa Jerman\nBahasa Prancis\nBahasa Jepang\nBahasa Korea\nBahasa Mandarin'});$('#requireConsent').prop('checked',true).trigger('change')})});
    function usePreset(config){
        Swal.fire({icon:'question',title:config.confirmTitle,text:'Identitas dan pertanyaan saat ini akan diganti.',showCancelButton:true,confirmButtonText:'Gunakan Preset',cancelButtonText:'Batal',confirmButtonColor:'#2563eb'}).then(r=>{
            if(!r.isConfirmed)return;
            $('#pollTitle').val(config.title);$('#pollDescription').val(config.description);
            $('[name="audience"][value="'+config.audience+'"]').prop('checked',true).trigger('change');
            if(config.audience==='both'){$('#studentAll,#gtkAll').prop('checked',true).trigger('change')}
            if(config.audience==='gtk'){$('#gtkAll').prop('checked',true).trigger('change')}
            $('#questionBuilder').empty();questionIndex=0;config.questions.forEach(addQuestion);
            $('#requireConsent').prop('checked',!!config.consent).trigger('change');
        });
    }
    $('#satisfactionPreset').on('click',()=>usePreset({confirmTitle:'Gunakan preset Survei Kepuasan?',title:'Survei Kepuasan Layanan',description:'Berikan penilaian dan masukan untuk membantu peningkatan mutu layanan sekolah.',audience:'both',consent:false,questions:[{prompt:'Bagaimana tingkat kepuasan Anda terhadap layanan?',type:'single',is_required:true,options_text:'Sangat Puas\nPuas\nCukup\nKurang Puas\nTidak Puas'},{prompt:'Saran atau masukan untuk perbaikan layanan',type:'long_text',is_required:false}]}));
    $('#confirmationPreset').on('click',()=>usePreset({confirmTitle:'Gunakan preset Konfirmasi Kegiatan?',title:'Konfirmasi Keikutsertaan Kegiatan',description:'Konfirmasi kesediaan dan catat keterangan responden untuk pelaksanaan kegiatan.',audience:'gtk',consent:true,questions:[{prompt:'Apakah Anda bersedia mengikuti kegiatan tersebut?',type:'yes_no',is_required:true},{prompt:'Keterangan atau kebutuhan khusus',type:'long_text',is_required:false}]}));
});
</script>
@stop
