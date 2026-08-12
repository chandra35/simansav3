@section('css')
<style>.counseling-form .form-section{padding-bottom:.55rem;margin-bottom:1rem;border-bottom:1px solid #e2e8f0;font-weight:700}.counseling-form textarea{resize:vertical}.select2-container--bootstrap4 .select2-selection{min-height:38px}</style>
@stop
@section('js')
<script>
$(function(){
    $('.select2').select2({theme:'bootstrap4',width:'100%'});
    $('#siswa_id').select2({theme:'bootstrap4',width:'100%',placeholder:'Ketik nama, NISN, atau NIS Lokal',minimumInputLength:2,ajax:{url:@json(route('admin.catatan-konseling.students.search')),dataType:'json',delay:300,data:p=>({q:p.term}),processResults:d=>d},templateResult:item=>item.loading?item.text:$('<div><strong></strong><br><small class="text-muted"></small></div>').find('strong').text(item.text).end().find('small').text('NISN '+(item.nisn||'-')+' · '+item.kelas).end()});
    const toggleReferral=()=>$('#rujukan_ke').prop('required',$('#status').val()==='perlu_rujukan');$('#status').on('change',toggleReferral);toggleReferral();
});
</script>
@stop
