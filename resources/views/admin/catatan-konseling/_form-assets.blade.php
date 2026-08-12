@section('css')
<style>.counseling-form .form-section{padding-bottom:.55rem;margin-bottom:1rem;border-bottom:1px solid #e2e8f0;font-weight:700}.counseling-form textarea{resize:vertical}.select2-container--bootstrap4 .select2-selection{min-height:38px}.counseling-form .bk-context__photo{width:78px;height:98px;object-fit:cover;border-radius:.55rem;border:2px solid #fff;box-shadow:0 0 0 1px #cbd5e1}.counseling-form .bk-context h4{font-size:1.05rem;font-weight:700;margin:.3rem 0}.counseling-form .bk-context__facts{background:#f8fafc;border:1px solid #e2e8f0;border-radius:.5rem;padding:.65rem 0}.counseling-form .bk-context__facts span,.counseling-form .bk-context__facts small{display:block;color:#64748b;font-size:.7rem}.counseling-form .bk-context__facts strong{display:block;font-size:.8rem;overflow-wrap:anywhere}.counseling-form .attendance-group .badge{margin:.15rem}.counseling-form .attendance-group>strong{display:inline-block;min-width:52px}.counseling-form .bk-notes{max-height:190px;overflow:auto}.counseling-form .bk-note+.bk-note{border-top:1px solid #e2e8f0;margin-top:.55rem;padding-top:.55rem}.counseling-form .bk-note small{color:#64748b;margin-left:.35rem}.counseling-form .bk-note p{margin:.25rem 0 0;font-size:.8rem}</style>
@stop
@section('js')
<script>
$(function(){
    $('.select2').select2({theme:'bootstrap4',width:'100%'});
    $('#siswa_id').select2({theme:'bootstrap4',width:'100%',placeholder:'Ketik nama, NISN, atau NIS Lokal',minimumInputLength:2,ajax:{url:@json(route('admin.catatan-konseling.students.search')),dataType:'json',delay:300,data:p=>({q:p.term}),processResults:d=>d},templateResult:item=>item.loading?item.text:$('<div><strong></strong><br><small class="text-muted"></small></div>').find('strong').text(item.text).end().find('small').text('NISN '+(item.nisn||'-')+' · '+item.kelas).end()});
    const toggleReferral=()=>$('#rujukan_ke').prop('required',$('#status').val()==='perlu_rujukan');$('#status').on('change',toggleReferral);toggleReferral();
    const toggleNotice=()=>$('#teacher_notice').prop('required',$('#share_with_teachers').is(':checked')).prop('disabled',!$('#share_with_teachers').is(':checked'));$('#share_with_teachers').on('change',toggleNotice);toggleNotice();
});
</script>
@stop
