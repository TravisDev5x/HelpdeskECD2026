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
        {{-- <div class="form-group-inline">
        <label for="producto">Producto: </label>
        <input type="text" name="producto" id="producto" class="form-control">
    </div> --}}
        <br>
      <table id="assignments-table" class="table table-bordered table-striped table-sm">
        <thead>
          <tr>
            <th>Empleado</th>
            <th>Producto</th>
            <th>Departamento</th>
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
    // var table = $('#assignments-table').DataTable();


    // $( "#producto" ).keyup(function() {
        var producto =  $( "#producto" ).val();
        // alert(producto);
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
      "bDestroy": true,
      language: {
        'url': '../js/spanish.json',
      },
    //   ajax: ,
      ajax: {
               "url": '{{ route('get_listassignments2') }}',
               "type": 'GET',
               "data": $('#producto').serializeArray()
           },

      columns: [
        {data: 'nombre_emple', name: 'nombre_emple'},
        {data: 'prod_name', name: 'products.name'},
        {data: 'dept_name', name: 'departments.name'},
        {data: 'cantidad', name: 'cantidad'},
        {
          data: null,
          searchable: false,
          'mRender': function (datos) {
            return '<a href="assignments/'+datos.employee_id+'/productos/'+datos.prod_name+'" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>';
          }
        },
      ],
    //   order: [[0, 'asc']],
      columnDefs: [
        // {orderable: false, targets: [3] },
        {searchable: false, targets: [3] },
        {className: "text-center", targets: [3]},
      ],
    });
    // });

    if($(window).width() < 576){
      $('#assignments-table').removeClass('nowrap');
    }
    else {
      $('#assignments-table').addClass('nowrap');
    }
  });
</script>
@endpush
