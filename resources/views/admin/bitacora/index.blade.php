@extends('admin.layout')

@section('title', '| Bitácora')

@section('header')
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">BITÁCORA
          <!-- <small>Listado</small> -->
        </h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
          <li class="breadcrumb-item active">Bitácora</li>
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
          <h3 class="card-title">Listado de bitácoras diarias</h3>
        </div>
      </div>
      {{-- <div class="d-flex bd-highlight">
        <div class="ml-auto bd-highlight form-inline">
          <h3 class="card-title form-group">
            <label for="fecha_i">Fecha inicial</label>
            <input type="date" id="fecha_i" name="fecha_i" class="form-control">
            &nbsp;
            <label for="fecha_f">Fecha final</label>
            <input type="date" id="fecha_f" name="fecha_f" class="form-control">
          </h3>
        </div>
      </div> --}}
    </div>
    <!-- /.card-header -->
    <div class="card-body">
      <table id="bitacoras-table" class="table table-bordered table-striped table-sm">
        <thead>
          <tr>
            <th>ID</th>
            <th>Usuario</th>
            <th>Actividad</th>
            <th>Descripción</th>
            <th>Fecha</th>
            <th>Duración</th>
            <th>Acciones</th>
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
    fecha_i = $('#fecha_i').val();
    fecha_f = $('#fecha_f').val();
    console.log(fecha_i);

    if($(window).width() < 576){
      $('#bitacoras-table').removeClass('nowrap');
    }
    else {
      $('#bitacoras-table').addClass('nowrap');
    }

    $('#bitacoras-table').DataTable({
      "processing": true,
      "serverSide": true,
      "paging": true,
      "lengthChange": true,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "responsive": true,
    "scrollX": true,
      "language": {
        'url': '../js/spanish.json',
      },
      "ajax" : {
          'url' : '{{ route("get_bitacoras") }}',
          'data' : {
              'fecha_i':fecha_i,
              'fecha_f':fecha_f
          }
          },
      "columns": [
        {data: 'id', name: 'id'},
        {data: 'name', name: 'name'},
        {data: 'actividad', name: 'actividad'},
        {data: 'descripcion', name: 'descripcion'},
        {data: 'fecha', name: 'fecha'},
        {data: 'duracion', name: 'duracion'},
        {
        data: null,
        searchable: false,
        'mRender': function (datos) {
          return '<a href="bitacora/'+datos.id+'" class="btn btn-xs btn-default mr-1"><i class="fa fa-eye"></i></a>'+
          '@can('create bitacoras')<a href="bitacora/'+datos.id+'/edit" class="btn btn-xs btn-info mr-1"><i class="fas fa-pencil-alt"></i></a>@endcan' +
          '@can('create bitacoras')<form method="post" action="bitacora/'+datos.id+'" style="display: inline;">'+
          '@method('delete')'+
          '@csrf'+
          '<button class="btn btn-xs btn-danger"'+
          'onclick="return confirm_delete()"><i class="fas fa-trash-alt"></i></button>'+
          '</form>@endcan';
        }
      },
      ],
    //   "order": [[0, 'asc']],
    //   "columnDefs": [
    //     {orderable: false, targets: [1] },
    //     {className: "text-center", targets: [1]},
    //   ],
    });
  });
</script>
@endpush
