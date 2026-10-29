@extends('adminlte::page')

@section('title', 'Scan QR Presensi')

@section('content_header')
<div class="d-flex align-items-center justify-content-between flex-wrap">
    <div><h1 class="mb-1"><i class="fas fa-qrcode text-primary mr-2"></i>Scan QR Presensi</h1><small class="text-muted">Gunakan kamera atau masukkan kode QR dari petugas.</small></div>
</div>
@stop

@section('content')
<div class="row justify-content-center qr-scan-page"><div class="col-lg-7"><div class="card shadow-sm border-0"><div class="card-body p-4 p-md-5">
    <div class="scan-intro text-center mb-4"><div class="scan-icon"><i class="fas fa-camera"></i></div><h4 class="font-weight-bold mt-3">Presensi mandiri yang aman</h4><p class="text-muted mb-0">QR hanya berlaku untuk sesi dan rombel Anda, lalu kedaluwarsa otomatis.</p></div>
    <div id="notice" class="d-none alert" role="alert"></div>
    <div class="form-group"><label for="qr-token">Kode QR</label><textarea id="qr-token" class="form-control" rows="3" placeholder="Arahkan kamera ke QR, atau tempelkan kode di sini." autocomplete="off"></textarea></div>
    <button id="submit-token" class="btn btn-primary btn-block py-2"><i class="fas fa-check-circle mr-1"></i>Catat Presensi</button>
    <div class="text-center mt-3"><button id="open-camera" class="btn btn-link"><i class="fas fa-camera mr-1"></i>Pindai dengan kamera</button></div>
    <div id="camera-wrap" class="d-none mt-3"><video id="camera" class="w-100 rounded" autoplay playsinline></video><p class="small text-muted text-center mt-2 mb-0">Arahkan kamera hingga QR terbaca.</p></div>
</div></div></div></div>
@stop

@section('css')
<style>.qr-scan-page .card{border-radius:18px}.scan-icon{width:72px;height:72px;border-radius:22px;margin:auto;display:grid;place-items:center;background:#eff6ff;color:#2563eb;font-size:2rem}.qr-scan-page textarea{border-radius:12px}.qr-scan-page video{max-height:360px;object-fit:cover;background:#0f172a}</style>
@stop

@section('js')
<script>
(() => {
 const token=document.getElementById('qr-token'), notice=document.getElementById('notice'), button=document.getElementById('submit-token'), video=document.getElementById('camera'), cameraWrap=document.getElementById('camera-wrap'); let stream;
 const show=(message,type)=>{notice.className='alert alert-'+type;notice.textContent=message;notice.classList.remove('d-none')};
 async function submit(){const value=token.value.trim();if(!value){show('Kode QR belum diisi.','warning');return}button.disabled=true;button.innerHTML='<i class="fas fa-spinner fa-spin mr-1"></i>Mencatat…';try{const response=await fetch('{{ route('siswa.qr-attendance.check-in') }}',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify({token:value})});const payload=await response.json();if(!response.ok){show(payload.message || Object.values(payload.errors || {}).flat()[0] || 'Presensi belum dapat dicatat.','danger');return}show(payload.message+' Status: '+payload.data.status+'.','success');stopCamera()}catch(error){show('Koneksi bermasalah. Coba lagi.','danger')}finally{button.disabled=false;button.innerHTML='<i class="fas fa-check-circle mr-1"></i>Catat Presensi'}}
 function stopCamera(){if(stream){stream.getTracks().forEach(track=>track.stop());stream=null}cameraWrap.classList.add('d-none')}
 async function camera(){if(!('BarcodeDetector' in window)){show('Pemindai kamera belum didukung browser ini. Masukkan kode QR secara manual.','info');return}try{stream=await navigator.mediaDevices.getUserMedia({video:{facingMode:{ideal:'environment'}}});video.srcObject=stream;cameraWrap.classList.remove('d-none');const detector=new BarcodeDetector({formats:['qr_code']});const scan=async()=>{if(!stream)return;try{const codes=await detector.detect(video);if(codes[0]?.rawValue){token.value=codes[0].rawValue;stopCamera();submit();return}}catch(e){}requestAnimationFrame(scan)};scan()}catch(error){show('Kamera tidak dapat diakses. Periksa izin kamera atau masukkan kode manual.','warning')}}
 button.addEventListener('click',submit);document.getElementById('open-camera').addEventListener('click',camera);window.addEventListener('beforeunload',stopCamera);
})();
</script>
@stop
