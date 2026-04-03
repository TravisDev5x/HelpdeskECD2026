@extends('admin.layout')

@section('title', '| Pruebas')

@section('header')
<div class="container-fluid">
  <div class="row mb-2">
    <div class="col-sm-6">
      <h1 class="m-0 text-dark">PRUEBAS
        <!-- <small>Listado</small> -->
      </h1>
    </div><!-- /.col -->
    <div class="col-sm-6">
      <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Pruebas</li>
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
        <h3 class="card-title">Listado de pruebas</h3>
      </div>
      <div class="bd-highlight pr-2">
        <a class="btn btn-primary btn-sm" href="{{ route('admin.tests.create') }}">
          <i class="fa fa-plus"></i> Crear prueba
        </a>
      </div>
    </div>
  </div>
  <!-- /.card-header -->
  <div class="card-body">
    <table id="tests-table" class="table table-bordered table-striped table-sm">
      <thead>
        <tr>
          <th>ID</th>
          <th>Activo</th>
          <th>Status</th>
          <th>Nivel</th>
          <th>Fecha y hora</th>
          <th>Observaciones</th>
          <th>Usuario</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($tests as $test)
        <tr>
          <td>{{ $test->id }}</td>
          <td>{{ $test->asset->name }}</td>
          <td>{{ $test->status }}</td>
          <td>{{ 'Nivel '.$test->nivel }}</td>
          <td>{{ $test->test_date }}</td>
          <td>{{ $test->observations }}</td>
          <td>{{ $test->user->name }}</td>
        </tr>
        @endforeach
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
    $('#tests-table').removeClass('nowrap');
  }
  else {
    $('#tests-table').addClass('nowrap');
  }

  $('#tests-table').DataTable({
    "processing": true,
    "paging": true,
    // "lengthChange": true,
    "searching": true,
    "ordering": true,
    "info": true,
    // "autoWidth": false,
    "scrollX": true,
    language: {
      'url': '../js/spanish.json',
    },
  });
});
</script>
@endpush
