@extends('adminlte::page')

@section('title', 'Kalender Akademik')

@section('content_header')
    <h1><i class="fas fa-calendar mr-2"></i>Kalender Akademik</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filter & Tambah</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Tahun Pelajaran</label>
                        <select id="filter-tahun" class="form-control">
                            <option value="">-- Semua --</option>
                            @foreach($tahunPelajaran as $tp)
                                <option value="{{ $tp->id }}" {{ $tp->is_aktif ? 'selected' : '' }}>
                                    {{ $tp->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    @can('manage-kalender-akademik')
                        <hr>
                        <button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#modal-event">
                            <i class="fas fa-plus mr-1"></i> Tambah Kegiatan
                        </button>
                    @endcan
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Keterangan Warna</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($kategori as $key => $label)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $label }}
                                <span class="badge" style="background-color: {{ \App\Models\KalenderAkademik::WARNA[$key] ?? '#3788d8' }}">&nbsp;&nbsp;&nbsp;</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
    
    @can('manage-kalender-akademik')
    <!-- Modal Event -->
    <div class="modal fade" id="modal-event" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="form-event">
                    @csrf
                    <input type="hidden" name="id" id="event-id">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Kegiatan</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Tahun Pelajaran <span class="text-danger">*</span></label>
                            <select name="tahun_pelajaran_id" id="event-tahun" class="form-control" required>
                                @foreach($tahunPelajaran as $tp)
                                    <option value="{{ $tp->id }}" {{ $tp->is_aktif ? 'selected' : '' }}>
                                        {{ $tp->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Nama Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_kegiatan" id="event-nama" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Kategori <span class="text-danger">*</span></label>
                            <select name="kategori" id="event-kategori" class="form-control" required>
                                @foreach($kategori as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Mulai <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_mulai" id="event-mulai" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Selesai</label>
                                    <input type="date" name="tanggal_selesai" id="event-selesai" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea name="deskripsi" id="event-deskripsi" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="is_libur" id="event-libur" class="custom-control-input" value="1">
                                <label class="custom-control-label" for="event-libur">Hari Libur</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger btn-delete-event" style="display:none;">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/id.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const canManageCalendar = @json(auth()->user()->can('manage-kalender-akademik'));
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'id',
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listMonth'
                },
                events: function(info, successCallback, failureCallback) {
                    $.ajax({
                        url: '{{ route("admin.kalender-akademik.events") }}',
                        data: {
                            start: info.startStr,
                            end: info.endStr,
                            tahun_pelajaran_id: $('#filter-tahun').val()
                        },
                        success: function(data) {
                            successCallback(data);
                        }
                    });
                },
                eventClick: function(info) {
                    if (!canManageCalendar) return;
                    var event = info.event;
                    $('#event-id').val(event.id);
                    $('#event-nama').val(event.title);
                    $('#event-kategori').val(event.extendedProps.kategori);
                    $('#event-mulai').val(event.startStr.split('T')[0]);
                    $('#event-selesai').val(event.endStr ? event.endStr.split('T')[0] : '');
                    $('#event-deskripsi').val(event.extendedProps.deskripsi);
                    $('#event-libur').prop('checked', event.extendedProps.is_libur);
                    $('.btn-delete-event').show();
                    $('.modal-title').text('Edit Kegiatan');
                    $('#modal-event').modal('show');
                },
                dateClick: function(info) {
                    if (!canManageCalendar) return;
                    resetForm();
                    $('#event-mulai').val(info.dateStr);
                    $('#modal-event').modal('show');
                },
                editable: canManageCalendar,
                eventDrop: function(info) {
                    updateEventDates(info.event);
                },
                eventResize: function(info) {
                    updateEventDates(info.event);
                }
            });
            
            calendar.render();
            
            $('#filter-tahun').change(function() {
                calendar.refetchEvents();
            });
            
            function resetForm() {
                $('#form-event')[0].reset();
                $('#event-id').val('');
                $('.btn-delete-event').hide();
                $('.modal-title').text('Tambah Kegiatan');
            }
            
            $('#modal-event').on('hidden.bs.modal', function() {
                resetForm();
            });
            
            $('#form-event').submit(function(e) {
                e.preventDefault();
                var id = $('#event-id').val();
                var url = id 
                    ? '{{ route("admin.kalender-akademik.index") }}/' + id 
                    : '{{ route("admin.kalender-akademik.store") }}';
                var method = id ? 'PUT' : 'POST';
                
                $.ajax({
                    url: url,
                    type: method,
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#modal-event').modal('hide');
                            calendar.refetchEvents();
                            toastr.success(response.message);
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Terjadi kesalahan');
                    }
                });
            });
            
            $('.btn-delete-event').click(function() {
                var id = $('#event-id').val();
                if (confirm('Apakah Anda yakin ingin menghapus kegiatan ini?')) {
                    $.ajax({
                        url: '{{ route("admin.kalender-akademik.index") }}/' + id,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            if (response.success) {
                                $('#modal-event').modal('hide');
                                calendar.refetchEvents();
                                toastr.success(response.message);
                            }
                        }
                    });
                }
            });
            
            function updateEventDates(event) {
                $.ajax({
                    url: '{{ route("admin.kalender-akademik.index") }}/' + event.id + '/dates',
                    type: 'PATCH',
                    data: {
                        _token: '{{ csrf_token() }}',
                        tanggal_mulai: event.startStr.split('T')[0],
                        tanggal_selesai: event.endStr ? event.endStr.split('T')[0] : null
                    },
                    success: function(response) {
                        toastr.success(response.message);
                    }
                });
            }
        });
    </script>
@stop
