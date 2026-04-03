@extends('admin.layout')

@section('title', '| Mantenimientos')

@section('header')
<div class="container-fluid">
  <div class="row mb-2">
    <div class="col-sm-6">
      <h1 class="m-0 text-dark">MANTENIMIENTOS
        <!-- <small>Listado</small> -->
      </h1>
    </div><!-- /.col -->
    <div class="col-sm-6">
      <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Inventario</a></li>
        <li class="breadcrumb-item active">Mantenimientos</li>
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
        <h3 class="card-title">Listado de mantenimientos</h3>
      </div>
    </div>
  </div>
  <!-- /.card-header -->
  <div class="card-body">
    <table id="maintenances-table" class="table table-bordered table-striped table-sm">
      <thead>
        <tr>
          <th>ID</th>
          <th>Equipo</th>
          <th>Serie</th>
          <th>Observaciones</th>
          <th>Fecha</th>
          <th>Responsable</th>
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
    $('#maintenances-table').removeClass('nowrap');
  }
  else {
    $('#maintenances-table').addClass('nowrap');
  }

  $('#maintenances-table').DataTable({
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
    ajax: 
     {
        "url": '{{ route('get_mantenances') }}',
        "type": 'GET',
          
    },
     columns: [
          // {data: 'created_at', name: 'created_at'},
          {data: 'id', name: 'id'},
          {data: 'product', name: 'product'},
          {data: 'serie', name: 'serie'},
          {data: 'maintenance', name: 'maintenance'},
          {data: 'maintenance_date', name: 'maintenance_date'},
          {data: 'nombre_emple', name: 'nombre_emple'},
          

        ],
        "search": { "regex": true } // Habilita la búsqueda con expresiones regulares

  });
});
</script>
@endpush
