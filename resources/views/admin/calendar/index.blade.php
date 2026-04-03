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
  <p class="text-muted small mb-2">
    <span class="d-inline-block mr-3"><span class="badge align-middle" style="background:#3788d8">&nbsp;</span> Personal (solo usted)</span>
    @if($canReadTeamCalendar)
      <span class="d-inline-block"><span class="badge align-middle" style="background:#6f42c1">&nbsp;</span> Equipo (visible según permisos de rol)</span>
    @endif
  </p>
  <div id="calendar" class="mb-3"></div>
  @include('admin.calendar.create')
  @include('admin.calendar.update')
@endsection

@push('styles')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fullcalendar/core@5.11.5/main.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@5.11.5/main.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@5.11.5/main.min.css">
  <style>#calendar { min-height: 70vh; }</style>
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
    if (!calendarEl) {
      return;
    }
    if (typeof FullCalendar === 'undefined') {
      calendarEl.innerHTML = '<div class="alert alert-warning">No se pudo cargar el calendario (FullCalendar). Revisa la conexión o la consola del navegador.</div>';
      return;
    }
    if (typeof jQuery === 'undefined' || typeof jQuery.ajax !== 'function') {
      calendarEl.innerHTML = '<div class="alert alert-danger">jQuery no está disponible; la agenda no puede cargar eventos.</div>';
      return;
    }
    jQuery.ajax({
      {{-- url() evita 500 si route:cache quedó sin los nombres admin.agenda.* --}}
      url: @json(url('admin/get_calendar')),
      type: 'GET',
      dataType: 'json',
    })
      .done(function(datos) {
        var eventList = Array.isArray(datos) ? datos : [];
        eventList = eventList.filter(function (ev) { return ev && ev.start; });
        var calendar;
        try {
          calendar = new FullCalendar.Calendar(calendarEl, {
          locale: 'es',
          initialView: 'dayGridMonth',
          height: 'auto',
          contentHeight: 620,
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
          },
          events: eventList,
          eventClick: function(info) {
            jQuery.ajax({
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
              var canEdit = datos.can_edit !== false && datos.can_edit !== 0;
              if (canEdit) {
                $('#actividad_update, #descripcion_update').prop('readonly', false);
                $('#date_end_update, #time_update').prop('readonly', false);
                $('#status_update').prop('disabled', false);
                $('#modalEvent .modal-title').text('Seguimiento');
              } else {
                $('#actividad_update, #descripcion_update').prop('readonly', true);
                $('#date_end_update, #time_update').prop('readonly', true);
                $('#status_update').prop('disabled', true);
                $('#modalEvent .modal-title').text('Evento (solo lectura)');
              }
              $('#modalEvent').find('button[type="submit"]').prop('disabled', !canEdit).toggle(canEdit);
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
        } catch (e) {
          calendarEl.innerHTML = '<div class="alert alert-danger">Error al inicializar el calendario. Si el problema continúa, abra la consola del navegador (F12) y recargue.</div>';
          if (window.console && console.error) {
            console.error(e);
          }
          return;
        }
        calendar.render();
      })
      .fail(function(xhr) {
        var msg = (xhr.status === 401 || xhr.status === 403)
          ? 'No autorizado para cargar eventos (' + xhr.status + '). Cierre sesión y vuelva a entrar.'
          : 'No se pudieron cargar los eventos (' + (xhr.status || 'error') + ').';
        calendarEl.innerHTML = '<div class="alert alert-danger">' + msg + '</div>';
      });
    jQuery('#modalEvent').on('hidden.bs.modal', function() {
      $('#actividad_update, #descripcion_update').prop('readonly', false);
      $('#date_end_update, #time_update').prop('readonly', false);
      $('#status_update').prop('disabled', false);
      $('#modalEvent').find('button[type="submit"]').prop('disabled', false).show();
      $('#modalEvent .modal-title').text('Seguimiento');
    });
  });
</script>
@endpush
