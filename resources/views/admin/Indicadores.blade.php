@extends('admin.layout')

@section('title', '| Servicios a Realizar')

@section('content')
  <section class="content">
    <div class="row">
      <div class="col-12">
        <div class="card card-primary card-outline shadow-sm"> <div class="card-header">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-ticket-alt mr-1"></i> Tickets a Realizar
            </h3>
          </div>
          <div class="card-body">

            {{-- Fila de Indicadores (Info Boxes) --}}
            {{-- Optimizado: 4 columas en escritorio, 2 en tablet, 1 en móvil --}}
            <div class="row">
              <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <div class="info-box shadow-sm">
                  <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-pen"></i></span>
                  <div class="info-box-content">
                    <span class="info-box-text">Generados</span>
                    <span class="info-box-number">{{ $generados }}</span>
                  </div>
                </div>
              </div>

              <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <div class="info-box mb-3 shadow-sm">
                  <span class="info-box-icon bg-danger elevation-1"><i class="far fa-clock"></i></span>
                  <div class="info-box-content">
                    <span class="info-box-text">Pendientes</span>
                    <span class="info-box-number">{{ $pendientes }}</span>
                  </div>
                </div>
              </div>

              <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <div class="info-box mb-3 shadow-sm">
                  <span class="info-box-icon bg-warning elevation-1 text-white"><i class="fas fa-hourglass-half"></i></span>
                  <div class="info-box-content">
                    <span class="info-box-text">En proceso</span>
                    <span class="info-box-number">{{ $seguimientos }}</span>
                  </div>
                </div>
              </div>

              <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <div class="info-box mb-3 shadow-sm">
                  <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-double"></i></span>
                  <div class="info-box-content">
                    <span class="info-box-text">Finalizados</span>
                    <span class="info-box-number">{{ $finalizados }}</span>
                  </div>
                </div>
              </div>
            </div>
            <hr>

            {{-- Formulario de Filtros --}}
            <form action="{{ route('admin.reports.download') }}" method="get">
              <div class="row d-flex align-items-end">
                <div class="form-group col-12 col-md-3">
                  <label for="fecha-inicio" class="small text-muted">Fecha Inicio</label>
                  <div class="input-group input-group-sm date" id="datepicker-inicio" data-target-input="nearest">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                    </div>
                    <input type="text" class="form-control datetimepicker-input" name="fechaInicio" id="fecha-inicio" value="{{ $hoy }}" data-target="#datepicker-inicio"/>
                  </div>
                </div>

                <div class="form-group col-12 col-md-3">
                  <label for="fecha-fin" class="small text-muted">Fecha Fin</label>
                  <div class="input-group input-group-sm date" id="datepicker-fin" data-target-input="nearest">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                    </div>
                    <input type="text" class="form-control datetimepicker-input" name="fechaFin" id="fecha-fin" value="{{ $hoy }}" data-target="#datepicker-fin"/>
                  </div>
                </div>

                <div class="form-group col-12 col-md-3">
                  <button type="submit" class="btn btn-primary btn-sm btn-block shadow-sm">
                    <i class="fas fa-download mr-1"></i> Descargar información
                  </button>
                </div>
              </div>
            </form>

            {{-- Gráficos --}}
            <div class="row mt-4">
              <div class="col-12 col-lg-6 mb-4">
                <div class="card shadow-none border">
                    <div class="card-body p-2">
                        <div id="areas"></div>
                    </div>
                </div>
              </div>
              <div class="col-12 col-lg-6 mb-4">
                <div class="card shadow-none border">
                    <div class="card-body p-2">
                        <div id="failures"></div>
                    </div>
                </div>
              </div>
              <div class="col-12 col-lg-6 mb-4">
                <div class="card shadow-none border">
                    <div class="card-body p-2">
                        <div id="users"></div>
                    </div>
                </div>
              </div>
              <div class="col-12 col-lg-6 mb-4">
                <div class="card shadow-none border">
                    <div class="card-body p-2">
                        <div id="days"></div>
                    </div>
                </div>
              </div>
              <div class="col-12 col-lg-6 mb-4">
                <div class="card shadow-none border">
                    <div class="card-body p-2">
                        <div id="areas-solution"></div>
                    </div>
                </div>
              </div>
              <div class="col-12 col-lg-6 mb-4">
                <div class="card shadow-none border">
                    <div class="card-body p-2">
                        <div id="users-solution"></div>
                    </div>
                </div>
              </div>
              <div class="col-12 col-lg-12 mb-4"> {{-- Tiempo suele requerir más ancho --}}
                <div class="card shadow-none border">
                    <div class="card-body p-2">
                        <div id="time"></div>
                    </div>
                </div>
              </div>
            </div>

            <pre id="tsv-area" class="d-none"></pre>
            <pre id="tsv-failure" class="d-none"></pre>
            <pre id="tsv-users" class="d-none"></pre>
            <pre id="tsv-days" class="d-none"></pre>
            <pre id="tsv-areas-solution" class="d-none"></pre>
            <pre id="tsv-users-solution" class="d-none"></pre>
            <pre id="tsv-time" class="d-none"></pre>

          </div>
        </div>
      </div>
    </div>
  </section>
@endsection


@push('styles')
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/datepiker/css/bootstrap-datepicker.min.css') }}">
  <link rel="stylesheet" href="{{ asset('css/highcharts.css') }}">
  <link rel="stylesheet" href="{{ asset('css/style-datatables.css') }}">
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">

  <style>
    /* Correcciones para Modo Oscuro (Dark Mode) */
    body.dark-mode .info-box {
        background-color: #343a40;
        color: #fff;
    }
    
    body.dark-mode .input-group-text {
        background-color: #3f474e;
        border-color: #6c757d;
        color: #fff;
    }

    body.dark-mode .form-control {
        background-color: #343a40;
        border-color: #6c757d;
        color: #fff;
    }
    
    body.dark-mode .form-control:focus {
        background-color: #3f474e;
    }

    /* Fix específico para Datepicker en modo oscuro */
    body.dark-mode .datepicker {
        background-color: #343a40 !important;
        color: #fff !important;
        border: 1px solid #6c757d;
    }
    
    body.dark-mode .datepicker table tr td.day:hover,
    body.dark-mode .datepicker table tr td.focused {
        background: #4b545c;
        cursor: pointer;
    }
    
    body.dark-mode .datepicker table tr td.active.active {
        background-color: #007bff !important;
    }

    body.dark-mode .card {
        background-color: #343a40;
        color: #fff;
    }
  </style>
@endpush

@push('scripts')
  <script src="{{ asset('adminlte/plugins/datepiker/js/bootstrap-datepicker.min.js') }}"></script>
  <script src="{{ asset('adminlte/plugins/moment/moment.min.js') }}"></script>
  <script src="{{ asset('highcharts/js/highcharts.js') }}"></script>
  <script src="{{ asset('highcharts/js/data.js') }}"></script>
  <script src="{{ asset('highcharts/js/drilldown.js') }}"></script>
  <script src="{{ asset('highcharts/js/sand-signika.js') }}"></script>
  <script src="{{ asset('highcharts/js/exporting.js') }}"></script>
  <script src="{{ asset('highcharts/js/export-data.js') }}"></script>
  <script src="{{ asset('js/sistema/reports_indicadores.js') }}"></script>

  <script>
      // function actualizar(){location.reload(true);}
      // //Función para actualizar cada 4 segundos(4000 milisegundos)
      // setInterval("actualizar()", 60000);
  </script>
@endpush