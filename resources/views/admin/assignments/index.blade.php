@extends('admin.layout')

@section('title', '| Asignación')

@section('header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">ASIGNACIÓN
                    <!-- <small>Listado</small> -->
                </h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Asignación</li>
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
                    <h3 class="card-title">Listado de equipos</h3>
                </div>
            </div>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <form action="{{ route('admin.assignments-masiva') }}" method="post" id="frm-example">
                @csrf
                @method('PUT')
                <label for="userMasiva">Usuario para asignación masiva</label>
                <select class="custom-select select2 mb-4" id="userMasiva" name="userMasiva">
                    <option value="" selected>Seleccionar usuarios...</option>
                    @foreach ($userSelect as $user)
                    <option value="{{$user->id}}">{{$user->name}}({{$user->usuario}})</option>
                @endforeach
                </select>
                <br>
                <br>
                <table id="example" class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th><input type="checkbox" name="select_all" value="1" id="example-select-all"></th>
                            {{-- <th>Acción</th> --}}
                            {{-- <th>ID</th> --}}
                            <th>Serie</th>
                            <th>Nombre</th>
                            <th>Etiqueta</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Empresa</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <button type="submit" class="btn btn-info btn-lg col-3 mt-3 rounded-lg border border-primary"><i class="fa fa-floppy-o" aria-hidden="true"></i>Guardar asignacion masiva</button>

            </form>
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
      <link rel="stylesheet" href="{{ asset('adminlte/plugins/select2/css/select2.min.css') }}">
@endpush

@push('scripts')
      <!-- DataTables -->
      <script src="{{ asset('adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
      <script src="{{ asset('adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
      <script src="{{ asset('adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
      <script src="{{ asset('adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

      <script src="{{ asset('adminlte/plugins/select2/js/select2.full.min.js') }}">
        
      </script>
  <script>

    
        $('.select2').select2({
    tags: false
  });
      $(function () {

        if($(window).width() < 576){
          $('#example').removeClass('nowrap');
        }
        else {
          $('#example').addClass('nowrap');
        }

        table = $('#example').DataTable({
          "processing": true,
        //   "serverSide": true,
          "deferRender": true,
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
          ajax: '{{ route('get_assignments') }}',
          columns: [
            {data: 'id', name: 'id'},
            // {
            //   data: null,
            //   searchable: false,
            //   'mRender': function (datos) {
            //     return '<a href="assignments/'+datos.id+'/edit" class="btn btn-xs btn-info">Asignar</a>';
            //   }
            // },
            {data: 'serie', name: 'serie'},
            {data: 'name', name: 'name'},
            {data: 'etiqueta', name: 'etiqueta'},
            {data: 'marca', name: 'marca'},
            {data: 'modelo', name: 'modelo'},
            {data: 'company.name', name: 'company.name'},
            {data: 'status', name: 'status'},
          ],
          order: [[3, 'asc']],
          columnDefs: [{
       'targets': 0,
       'searchable':false,
       'orderable':false,
       'className': 'dt-body-center',
       'render': function (data, type, full, meta){
           return '<input type="checkbox" name="id[]" value="' + $('<div/>').text(data).html() + '">';
       }
    }],
        });

        // Handle click on "Select all" control
   $('#example-select-all').on('click', function(){
      // Check/uncheck all checkboxes in the table
      var rows = table.rows({ 'search': 'applied' }).nodes();
      $('input[type="checkbox"]', rows).prop('checked', this.checked);
   });

   // Handle click on checkbox to set state of "Select all" control
   $('#example tbody').on('change', 'input[type="checkbox"]', function(){
      // If checkbox is not checked
      if(!this.checked){
         var el = $('#example-select-all').get(0);
         // If "Select all" control is checked and has 'indeterminate' property
         if(el && el.checked && ('indeterminate' in el)){
            // Set visual state of "Select all" control
            // as 'indeterminate'
            el.indeterminate = true;
         }
      }
   });

   $('#frm-example').on('submit', function(e){
      var form = this;

      // Iterate over all checkboxes in the table
      table.$('input[type="checkbox"]').each(function(){
         // If checkbox doesn't exist in DOM
         if(!$.contains(document, this)){
            // If checkbox is checked
            if(this.checked){
               // Create a hidden element
               $(form).append(
                  $('<input>')
                     .attr('type', 'hidden')
                     .attr('name', this.name)
                     .val(this.value)
               );
            }
         }
      });

      // FOR TESTING ONLY

      // Output form data to a console
    //   console.log("Form submission", $(form).serialize());

      // Prevent actual form submission
    //   e.preventDefault();
      });


      });
    </script>
@endpush
