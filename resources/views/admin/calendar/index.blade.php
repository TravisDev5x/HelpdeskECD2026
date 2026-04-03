@extends('admin.layout')

@section('title', '| Agenda')

@section('header')
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Agenda</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
          <li class="breadcrumb-item active">Agenda</li>
        </ol>
      </div>
    </div>
  </div>
@endsection

@section('content')
  {{-- FullCalendar antes cargaba desde adminlte/plugins/fullcalendar (no existe en public); CDN 5.x compatible con el script actual --}}
  <div id="calendar" class="mb-3"></div>
  @include('admin.calendar.create')
  @include('admin.calendar.update')
@endsection

@push('styles')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fullcalendar/core@5.11.5/main.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@5.11.5/main.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@5.11.5/main.min.css">
  <style>#calendar { min-height: 620px; }</style>
@endpush

@push('scripts')
  {{-- main.global.min.js expone window.FullCalendar; main.min.js es CJS y no define el global en el navegador --}}
  <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@5.11.5/main.global.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@5.11.5/main.global.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@5.11.5/main.global.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@5.11.5/main.global.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/locales-all.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    if (typeof FullCalendar === 'undefined') {
      calendarEl.innerHTML = '<div class="alert alert-warning">No se pudo cargar el calendario (FullCalendar). Revisa la conexión o la consola del navegador.</div>';
      return;
    }
    $.ajax({
      {{-- url() evita 500 si route:cache quedó sin los nombres admin.agenda.* --}}
      url: @json(url('admin/get_calendar')),
      type: 'GET',
    })
      .done(function(datos) {
        var calendar = new FullCalendar.Calendar(calendarEl, {
          locale: 'es',
          initialView: 'dayGridMonth',
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
          },
          events: [
            @foreach ($ids as $id)
              datos[{{ $id }}],
            @endforeach
          ],
          eventClick: function(info) {
            $.ajax({
              url: @json(url('admin/get-event')),
              type: 'GET',
              dataType: 'JSON',
              data: {'id': info.event.id},
            })
            .done(function(datos) {
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
            if (info.date >= new Date(new Date().setHours(0,0,0,0))) {
              $('#date').html(info.dateStr);
              $('#date_post').val(info.dateStr);
              $('#modalCalendar').modal('show');
            }
          },
          plugins: [ 'dayGrid', 'timeGrid', 'interaction' ],
        });
        calendar.render();
      })
      .fail(function(xhr) {
        calendarEl.innerHTML = '<div class="alert alert-danger">No se pudieron cargar los eventos (' + (xhr.status || 'error') + ').</div>';
      });
  });
</script>
@endpush
