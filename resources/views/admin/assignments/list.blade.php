@extends('admin.layout')

@section('title', '| Lista de asignaciones')

@section('header')
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">ASIGNACIONES
          <!-- <small>Listado</small> -->
        </h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
          <li class="breadcrumb-item active">Lista de asignaciones</li>
        </ol>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
@endsection

@section('content')
  <div class="card">
    <div class="card-header">
      <div class="d-flex bd-highlight">
        <div class="mr-auto bd-highlight">
          <h3 class="card-title">Listado de asignaciones</h3>
        </div>
      </div>
    </div>
    <!-- /.card-header -->
    <div class="card-body">
      <table id="assignments-table" class="table table-bordered table-striped table-sm">
        <thead>
          <tr>
            <th>Empleado</th>
            <th>No. empleado</th>
            <th>Departamento</th>
            @role('Mantenimiento')
              <th>Ubicacion</th>
            @endrole
            <th>Equipos</th>
            <th>&nbsp;</th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->
@endsection

@push('styles')
  <!-- DataTables -->
  <link rel="stylesheet" href="{{ asset('css/style-datatables.css') }}">
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
@endpush

@push('scripts')
  <!-- DataTables -->
  <script src="{{ asset('adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
  <script src="{{ asset('adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
  <script src="{{ asset('adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
  <script src="{{ asset('adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

  <script>
  $(function () {

    if($(window).width() < 576){
      $('#assignments-table').removeClass('nowrap');
    }
    else {
      $('#assignments-table').addClass('nowrap');
    }

    $('#assignments-table').DataTable({
      "processing": true,
      "serverSide": true,
      "paging": true,
      "lengthChange": true,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "responsive": true,
      language: {
        'url': '../js/spanish.json',
      },
      ajax: '{{ route('get_listassignments') }}',
      columns: [
        {data: 'nombre_emple', name: 'nombre_emple'},
        {data: 'usuario', name: 'users.usuario'},
        {data: 'dept_name', name: 'departments.name'},
        @role('Mantenimiento')
        {data: 'ubicacion_id', name: 'ubicacion_id'},
        @endrole
        {data: 'cantidad', name: 'cantidad'},
        {
          data: null,
          searchable: false,
          'mRender': function (datos) {
            return '<a href="assignments/'+datos.employee_id+'" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>';
          }
        },
      ],
    //   order: [[0, 'asc']],
      columnDefs: [
        {orderable: false, targets: [3] },
        {searchable: false, targets: [3] },
        {className: "text-center", targets: [3]},
      ],
    });
  });
</script>
@endpush
