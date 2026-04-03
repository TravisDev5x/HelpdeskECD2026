@extends('admin.layout')

@section('title', '| Lista de asignaciones')

@section('header')
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">DID
          <!-- <small>Listado</small> -->
        </h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
          <li class="breadcrumb-item active">Listado de DID</li>
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
          <h3 class="card-title">Listado de DID</h3>
        </div>
        <div class="bd-highlight pr-2">
        <a class="btn btn-primary btn-sm" href="{{ route('did.create') }}">
          <i class="fa fa-plus"></i> Crear did
        </a>
      </div>
      </div>
    </div>
    <!-- /.card-header -->
    <div class="card-body">
      <table id="services-table" class="table table-bordered table-striped table-sm table-condensed table-fixed" style="text-transform:uppercase;">
        <thead>
          <tr>
            <th>#</th>
            <th>Numero</th>
            <th>Cuenta</th>
            <th>Proveedor</th>
            <th>Tipo</th>
            
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



 if($(window).width() < 576){
    $('#services-table').removeClass('nowrap');
  }
  else {
    $('#services-table').addClass('nowrap');
  }
  $(function () {
    $('body').tooltip({selector: '[data-toggle="tooltip"]'});
    $('#services-table').DataTable({
      "processing": true,
      "paging": true,
      "lengthChange": true,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "responsive": true,
      
      ajax: 
       {
          "url": '{{ route('get_did') }}',
          "type": 'GET',
            
      },
       columns: [
          
            {
              data: null,
              searchable: false,
              'mRender': function (datos) {

             return boton = '<a href="/admin/did/show/'+datos.id+'" class="btn btn-info btn-xs" title="Gestion"> <i class="fas fa-edit"></i></a>';
              
              }
            },
           
            // {data: 'created_at', name: 'created_at'},
            {data: 'did', name: 'did'},
            {data: 'cuenta', name: 'cuenta'},
            {data: 'proveedor', name: 'proveedor'},
            {data: 'tipo', name: 'tipo'},

          ],
      columnDefs: [
        {className: "text-center", targets: [1]},
        {orderable: false, targets: [1] },
        // {className: "none", targets: [8, 9, 10]},
      ],
    });
  });
</script>
@endpush
