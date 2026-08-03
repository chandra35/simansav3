@extends('adminlte::page')

@php
    $editing = isset($polling);
    $selected = fn($type, $audience) => $editing ? $polling->targets->where('audience_type', $audience)->where('scope_type', $type)->pluck('scope_value')->all() : [];
    $questions = old('questions', $editing ? $polling->questions->map(fn($q) => [
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
        <div class="card bg-gradient-primary text-white mb-4 simansa-form-hero"><div class="card-body"><div class="row align-items-center"><div class="col-lg-8"><div class="small font-weight-bold text-uppercase mb-2"><i class="fas fa-magic mr-1"></i> Builder Polling</div><h2 class="h3 font-weight-bold mb-2">Rancang respons yang terukur</h2><p class="mb-0">Atur identitas, target, pertanyaan, aturan pilihan, persetujuan, dan jadwal publikasi.</p></div><div class="col-lg-4 mt-3 mt-lg-0 text-lg-right"><button type="button" id="tkaPreset" class="btn btn-light"><i class="fas fa-graduation-cap mr-1"></i> Preset TKA Kelas XII</button></div></div></div></div>

        @if($errors->any())<div class="alert alert-danger"><strong>Periksa kembali form:</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card card-outline card-primary"><div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-info-circle text-primary mr-2"></i>Identitas & Jadwal</h3></div><div class="card-body">
                    <div class="form-group"><label>Nama Polling <span class="text-danger">*</span></label><input name="title" id="pollTitle" class="form-control" maxlength="255" required value="{{ old('title',$polling->title??'') }}" placeholder="Contoh: Pemilihan Mata Pelajaran TKA 2026"></div>
                    <div class="form-group"><label>Deskripsi</label><textarea name="description" id="pollDescription" class="form-control" rows="3" placeholder="Jelaskan tujuan dan petunjuk singkat polling">{{ old('description',$polling->description??'') }}</textarea></div>
                    <div class="row"><div class="col-md-4 form-group"><label>Responden <span class="text-danger">*</span></label><select name="audience" id="audience" class="form-control" required>@foreach(['siswa'=>'Siswa','gtk'=>'GTK','both'=>'Siswa & GTK'] as $key=>$label)<option value="{{ $key }}" @selected(old('audience',$polling->audience??'siswa')===$key)>{{ $label }}</option>@endforeach</select></div><div class="col-md-4 form-group"><label>Mulai <span class="text-danger">*</span></label><input type="datetime-local" name="starts_at" class="form-control" required value="{{ old('starts_at',isset($polling)?$polling->starts_at->format('Y-m-d\TH:i'):now()->addHour()->format('Y-m-d\TH:i')) }}"></div><div class="col-md-4 form-group"><label>Selesai <span class="text-danger">*</span></label><input type="datetime-local" name="ends_at" class="form-control" required value="{{ old('ends_at',isset($polling)?$polling->ends_at->format('Y-m-d\TH:i'):now()->addDays(7)->format('Y-m-d\TH:i')) }}"></div></div>
                </div></div>

                <div class="card card-outline card-primary"><div class="card-header d-flex align-items-center"><h3 class="card-title font-weight-bold flex-grow-1"><i class="fas fa-question-circle text-primary mr-2"></i>Pertanyaan</h3><button type="button" class="btn btn-sm btn-primary" id="addQuestion"><i class="fas fa-plus mr-1"></i>Tambah Pertanyaan</button></div><div class="card-body"><div id="questionBuilder"></div></div></div>
            </div>

            <div class="col-lg-4">
                <div class="card card-outline card-primary"><div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-bullseye text-primary mr-2"></i>Target Responden</h3></div><div class="card-body">
                    <div id="studentTargets">
                        <h6 class="font-weight-bold"><i class="fas fa-user-graduate mr-1"></i>Siswa</h6>
                        <div class="custom-control custom-checkbox mb-3"><input type="checkbox" class="custom-control-input target-all" id="studentAll" name="student_all" value="1" @checked(old('student_all',$editing && in_array('all',$polling->targets->where('audience_type','siswa')->pluck('scope_type')->all())))><label class="custom-control-label" for="studentAll">Semua siswa aktif</label></div>
                        <label class="small font-weight-bold">Tingkat</label><div class="d-flex flex-wrap mb-3">@foreach([10=>'X',11=>'XI',12=>'XII'] as $grade=>$roman)<div class="custom-control custom-checkbox mr-3"><input class="custom-control-input student-scope" type="checkbox" name="student_grades[]" value="{{ $grade }}" id="grade{{ $grade }}" @checked(in_array((string)$grade,array_map('strval',old('student_grades',$selected('tingkat','siswa')))))><label class="custom-control-label" for="grade{{ $grade }}">{{ $roman }}</label></div>@endforeach</div>
                        <div class="form-group"><label class="small font-weight-bold">Rombel tertentu</label><select name="student_classes[]" class="form-control student-scope" multiple size="6">@foreach($classes as $class)<option value="{{ $class->id }}" @selected(in_array($class->id,old('student_classes',$selected('kelas','siswa'))))>{{ $class->nama_kelas }} · Tingkat {{ $class->tingkat }}</option>@endforeach</select><small class="text-muted">Gunakan Ctrl untuk memilih lebih dari satu.</small></div>
                    </div>
                    <div id="gtkTargets" class="border-top pt-3 mt-3">
                        <h6 class="font-weight-bold"><i class="fas fa-chalkboard-teacher mr-1"></i>GTK</h6>
                        <div class="custom-control custom-checkbox mb-3"><input type="checkbox" class="custom-control-input target-all" id="gtkAll" name="gtk_all" value="1" @checked(old('gtk_all',$editing && in_array('all',$polling->targets->where('audience_type','gtk')->pluck('scope_type')->all())))><label class="custom-control-label" for="gtkAll">Semua GTK aktif</label></div>
                        <div class="form-group"><label class="small font-weight-bold">Jenis PTK</label><select name="gtk_types[]" class="form-control gtk-scope" multiple size="5">@foreach($gtkTypes as $type)<option value="{{ $type }}" @selected(in_array($type,old('gtk_types',$selected('jenis_ptk','gtk'))))>{{ $type }}</option>@endforeach</select></div>
                        <div class="form-group"><label class="small font-weight-bold">Role</label><select name="gtk_roles[]" class="form-control gtk-scope" multiple size="4">@foreach($roles as $role)<option value="{{ $role->name }}" @selected(in_array($role->name,old('gtk_roles',$selected('role','gtk'))))>{{ $role->name }}</option>@endforeach</select></div>
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

        <div class="card"><div class="card-body d-flex flex-wrap justify-content-between"><a href="{{ route('admin.polling.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i>Kembali</a><div><button name="action" value="draft" class="btn btn-outline-primary mr-2"><i class="fas fa-save mr-1"></i>Simpan Draft</button><button name="action" value="publish" class="btn btn-primary"><i class="fas fa-paper-plane mr-1"></i>Simpan & Terbitkan</button></div></div></div>
    </form>
</div>
@stop

@section('css')
<style>
.simansa-polling-form .simansa-form-hero{border:0;border-radius:18px;overflow:hidden}.simansa-polling-form .question-card{border:1px solid #dbe3ef;border-left:4px solid #3b82f6;border-radius:12px;padding:1rem;margin-bottom:1rem;background:#f8fafc}.simansa-polling-form .question-number{width:30px;height:30px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:#dbeafe;color:#1d4ed8;font-weight:800}.simansa-polling-form label{color:#334155}.simansa-polling-form select[multiple]{min-height:110px}@media(max-width:575.98px){.simansa-polling-form .card-body{padding:1rem}.simansa-polling-form .card-body.d-flex{gap:.75rem}.simansa-polling-form .card-body.d-flex>div{display:flex;width:100%}.simansa-polling-form .card-body.d-flex>div .btn{flex:1}}
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
    function toggleAudience(){const a=$('#audience').val();$('#studentTargets').toggle(a==='siswa'||a==='both');$('#gtkTargets').toggle(a==='gtk'||a==='both')}
    function toggleAll(box,selector){$(selector).prop('disabled',box.checked)} $('#audience').on('change',toggleAudience); $('#studentAll').on('change',function(){toggleAll(this,'.student-scope')}); $('#gtkAll').on('change',function(){toggleAll(this,'.gtk-scope')}); $('#requireConsent').on('change',()=>$('#consentBox').toggle($('#requireConsent').prop('checked'))); toggleAudience(); $('#studentAll,#gtkAll,#requireConsent').trigger('change');
    $('#tkaPreset').on('click',function(){Swal.fire({icon:'question',title:'Gunakan preset TKA?',text:'Identitas dan pertanyaan saat ini akan diganti dengan contoh pemilihan dua mapel TKA kelas XII.',showCancelButton:true,confirmButtonText:'Gunakan Preset',cancelButtonText:'Batal',confirmButtonColor:'#2563eb'}).then(r=>{if(!r.isConfirmed)return;$('#pollTitle').val('Pemilihan Mata Pelajaran Pilihan TKA 2026');$('#pollDescription').val('Pilih tepat dua mata pelajaran pilihan Tes Kemampuan Akademik yang tercatat di rapor serta sesuai minat dan rencana studi lanjut.');$('#audience').val('siswa').trigger('change');$('#studentAll').prop('checked',false).trigger('change');$('#grade12').prop('checked',true);$('#questionBuilder').empty();questionIndex=0;addQuestion({prompt:'Pilih tepat dua mata pelajaran pilihan TKA',type:'multiple',is_required:true,min_selections:2,max_selections:2,options_text:'Matematika Tingkat Lanjut\nBahasa Indonesia Tingkat Lanjut\nBahasa Inggris Tingkat Lanjut\nFisika\nKimia\nBiologi\nEkonomi\nSosiologi\nGeografi\nSejarah\nAntropologi\nPPKn/Pendidikan Pancasila\nBahasa Arab\nBahasa Jerman\nBahasa Prancis\nBahasa Jepang\nBahasa Korea\nBahasa Mandarin'});$('#requireConsent').prop('checked',true).trigger('change')})});
});
</script>
@stop
