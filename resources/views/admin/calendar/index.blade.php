@extends('admin.layout')

@section('title', '| Agenda')

@section('header')
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Agenda
          <!-- <small>Listado</small> -->
        </h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
          <li class="breadcrumb-item active">Agenda</li>
        </ol>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
@endsection

@section('content')
  <div id='calendar'></div>
@endsection

  @include('admin.calendar.create')
  @include('admin.calendar.update')

@push('styles')
  <!-- DataTables -->
  <link rel="stylesheet" href="{{ asset('css/style-datatables.css') }}">
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/fullcalendar/main.css') }}">
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/fullcalendar-daygrid/main.css') }}">
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/fullcalendar-timegrid/main.css') }}">
 
 
@endpush

@push('scripts')
  <!-- DataTables -->
  <script src="{{ asset('adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
  <script src="{{ asset('adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
  <script src="{{ asset('adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
  <script src="{{ asset('adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
  <script src="{{ asset('adminlte/plugins/fullcalendar/main.js') }}"></script>
  <script src="{{ asset('adminlte/plugins/fullcalendar/locales-all.js') }}"></script>
  <script src="{{ asset('adminlte/plugins/fullcalendar-daygrid/main.js') }}"></script>
  <script src="{{ asset('adminlte/plugins/fullcalendar-timegrid/main.js') }}"></script>
  <script src="{{ asset('adminlte/plugins/fullcalendar-interaction/main.js') }}"></script>


<script>
  document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    let eventos;
    $.ajax({
      url: 'get_calendar',
      type: 'GET',
    })
      .done(function(datos)
    {
    var calendar = new FullCalendar.Calendar(calendarEl, {
      events: [
        @foreach ($ids as $id)
          datos[{{ $id }}],
        @endforeach
      ],

      eventClick: function(info) {                      
        $.ajax({
          url: 'get-event',
          type: 'GET',
          dataType: 'JSON',
          data: {'id': info.event.id},
        })
        .done(function(datos)
        {
                           
          $('#id_update').val(datos.id);
          $('#actividad_update').val(datos.actividad);
          $('#descripcion_update').val(datos.descripcion);
          $('#date_end_update').val(datos.end_date);
          $('#time_update').val(datos.hora);
          $('#status_update').val(datos.status);
          $('#date_update').html(datos.start_date);
          $('#date_post_update').val(datos.start_date);
          $('#modalEvent').modal('show');
                       
        });            
        info.el.style.borderColor = 'red';
      },

      dateClick: function(info) {
        console.log(info.date, Date.now())
        if (info.date >= Date.now()) {

          $('#date').html(info.dateStr);
          $('#date_post').val(info.dateStr);
          $('#modalCalendar').modal('show');
        }
      },
      plugins: [ 'dayGrid',  'timeGrid', 'interaction' ], 
    });

    calendar.render();
    });
  });
</script>
@endpush
