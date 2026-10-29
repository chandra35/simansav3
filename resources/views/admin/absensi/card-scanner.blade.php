@extends('adminlte::page')

@section('title', 'Pemindai Kartu Siswa')

@section('content_header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
    <div><h1 class="mb-1"><i class="fas fa-id-card text-primary mr-2"></i>Pemindai Kartu Siswa</h1><small class="text-muted">Pindai QR pada ID Card siswa. Sistem menentukan seluruh sesi presensi yang berlaku secara otomatis.</small></div>
    @can('view-student-attendance')
        <button id="create-access-link" class="btn btn-outline-primary mt-2 mt-md-0"><i class="fas fa-link mr-1"></i>Buat tautan petugas</button>
    @endcan
</div>
@stop

@section('content')
<div class="row justify-content-center card-scanner-page"><div class="col-xl-8"><div class="card shadow-sm border-0"><div class="card-body p-4 p-md-5">
    <div class="scanner-intro text-center mb-4"><div class="scanner-icon"><i class="fas fa-qrcode"></i></div><h4 class="font-weight-bold mt-3">Scan kartu, presensi tercatat</h4><p class="text-muted mb-0">Kartu siswa dipakai untuk absensi harian seluruh kelas aktif dan satu presensi sholat yang sesuai.</p></div>
    <div id="notice" class="d-none alert" role="alert"></div>
    <div class="form-group"><label for="card-qr">QR ID Card Siswa</label><textarea id="card-qr" class="form-control" rows="3" placeholder="Arahkan kamera ke QR pada ID Card siswa, atau gunakan scanner USB." autocomplete="off"></textarea></div>
    <div class="row"><div class="col-md-6 mb-2"><button id="scan-card" class="btn btn-primary btn-block py-2"><i class="fas fa-check-circle mr-1"></i>Catat Presensi</button></div><div class="col-md-6 mb-2"><button id="open-camera" class="btn btn-outline-primary btn-block py-2"><i class="fas fa-camera mr-1"></i>Scan dengan Kamera</button></div></div>
    <div id="camera-wrap" class="d-none mt-3"><video id="camera" class="w-100 rounded" autoplay playsinline></video><p class="small text-muted text-center mt-2 mb-0">Arahkan kamera ke QR pada kartu siswa.</p></div>
    @can('view-student-attendance')
    <div id="access-link-panel" class="d-none mt-4 p-3 rounded bg-light"><strong><i class="fas fa-mobile-alt mr-1"></i>Tautan satu kali untuk petugas</strong><p class="small text-muted mt-1 mb-2">Kirimkan tautan ini kepada petugas. Petugas harus login, lalu tautan hanya dapat digunakan sekali dan berlaku 30 menit.</p><div class="input-group"><input id="access-link" class="form-control" readonly><div class="input-group-append"><button id="copy-access-link" class="btn btn-outline-secondary" type="button">Salin</button></div></div></div>
    @endcan
</div></div></div></div>
@stop

@section('css')
<style>.card-scanner-page .card{border-radius:18px}.scanner-icon{width:76px;height:76px;border-radius:24px;margin:auto;display:grid;place-items:center;background:#eff6ff;color:#2563eb;font-size:2rem}.card-scanner-page textarea{border-radius:12px}.card-scanner-page video{max-height:420px;object-fit:cover;background:#0f172a}</style>
@stop

@section('js')
<script>
(() => {
 const field=document.getElementById('card-qr'),notice=document.getElementById('notice'),button=document.getElementById('scan-card'),video=document.getElementById('camera'),cameraWrap=document.getElementById('camera-wrap');let stream;
 const csrf=document.querySelector('meta[name="csrf-token"]').content;
 const show=(message,type)=>{notice.className='alert alert-'+type;notice.textContent=message;notice.classList.remove('d-none')};
 async function scan(){const cardQr=field.value.trim();if(!cardQr){show('QR ID Card belum dipindai.','warning');return}button.disabled=true;button.innerHTML='<i class="fas fa-spinner fa-spin mr-1"></i>Mencatat…';try{const response=await fetch('{{ route('card-scanner.scan') }}',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({card_qr:cardQr})});const payload=await response.json();if(!response.ok){show(payload.message||Object.values(payload.errors||{}).flat()[0]||'Presensi belum dapat dicatat.','danger');return}show(payload.message,'success');field.value='';stopCamera();field.focus()}catch(error){show('Koneksi bermasalah. Coba lagi.','danger')}finally{button.disabled=false;button.innerHTML='<i class="fas fa-check-circle mr-1"></i>Catat Presensi'}}
 function stopCamera(){if(stream){stream.getTracks().forEach(track=>track.stop());stream=null}cameraWrap.classList.add('d-none')}
 async function camera(){if(!('BarcodeDetector' in window)){show('Pemindai kamera belum didukung browser ini. Gunakan scanner USB atau tempelkan isi QR kartu.','info');return}try{stream=await navigator.mediaDevices.getUserMedia({video:{facingMode:{ideal:'environment'}}});video.srcObject=stream;cameraWrap.classList.remove('d-none');const detector=new BarcodeDetector({formats:['qr_code']});const detect=async()=>{if(!stream)return;try{const codes=await detector.detect(video);if(codes[0]?.rawValue){field.value=codes[0].rawValue;scan();return}}catch(error){}requestAnimationFrame(detect)};detect()}catch(error){show('Kamera tidak dapat diakses. Periksa izin kamera atau gunakan scanner USB.','warning')}}
 button.addEventListener('click',scan);document.getElementById('open-camera').addEventListener('click',camera);field.addEventListener('keydown',event=>{if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();scan()}});window.addEventListener('beforeunload',stopCamera);
 const create=document.getElementById('create-access-link');if(create){create.addEventListener('click',async()=>{const response=await fetch('{{ route('card-scanner.access-link') }}',{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf}});const payload=await response.json();if(!response.ok){show(payload.message||'Tautan tidak dapat dibuat.','danger');return}document.getElementById('access-link').value=payload.data.url;document.getElementById('access-link-panel').classList.remove('d-none')});document.getElementById('copy-access-link').addEventListener('click',async()=>{await navigator.clipboard.writeText(document.getElementById('access-link').value);show('Tautan petugas disalin.','info')})}
})();
</script>
@stop
