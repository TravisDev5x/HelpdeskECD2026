@extends('admin.layout')

@section('title', '| Eliminar asignaciones')

@section('header')
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">ELIMINAR ASIGNACIONES
          <!-- <small>Listado</small> -->
        </h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
          <li class="breadcrumb-item active">Eliminar asignaciones</li>
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
        <form action="{{ route('admin.assignments-destroyMasiva') }}" method="post" id="frm-example">
            @csrf
            @method('DELETE')

      <table id="assignments-table" class="table table-bordered table-striped table-sm">
        <thead>
          <tr>
            <th><input type="checkbox" name="select_all" value="1" id="example-select-all"></th>
            {{-- <th>&nbsp;</th> --}}
            <th>Serie</th>
            <th>Nombre</th>
            <th>Etiqueta</th>
            <th>Marca</th>
            <th>Modelo</th>
            <th>Empresa</th>
            <th>Empleado</th>
            <th>No.</th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
      <button type="submit" class="btn btn-danger btn-lg col-3 mt-3 rounded-lg"><i class="fa fa-floppy-o" aria-hidden="true"></i>Desasignar masivamente</button>
    </form>
    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->

  <!-- Modal -->
  <div class="modal fade" id="modalRemove" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form method="post" id="form-remove">
        @method('delete')
        @csrf
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Desasignar equipo</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="form-row">
              <div class="form-group col-md-12">
                <label for="observations">Observaciones</label>
                <textarea name="observations" class="form-control" rows="3"></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro de querer eliminar esta asignación?')">Desasignar</button>
          </div>
        </div>
      </form>
    </div>
  </div>
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

    table = $('#assignments-table').DataTable({
      "processing": true,
    //   "serverSide": true,
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
      ajax: '{{ route('get_desassignments') }}',
      columns: [
        {data: 'id', name: 'id'},
        // {
        //   data: null,
        //   searchable: false,
        //   'mRender': function (datos) {
        //     return '@can('delete assignment')<button type="button" class="btn btn-danger btn-xs" onclick="desasignar(' + "'" + datos.id + "'" + ')">Desasignar</button>@endcan';
        //   }
        // },
        {data: 'serie', name: 'serie'},
        {data: 'name', name: 'name'},
        {data: 'etiqueta', name: 'etiqueta'},
        {data: 'marca', name: 'marca'},
        {data: 'modelo', name: 'modelo'},
        {data: 'company.name', name: 'company.name'},
        {data: 'employee.name', name: 'employee.name'},
        {data: 'employee.usuario', name: 'employee.usuario'}
      ],
      order: [[0, 'asc']],
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
  });

  function desasignar(id) {
    $("#form-remove").attr('action', 'assignments/'+id);
    $('#modalRemove').modal('show');
  }

  function confirm_delete() {
    return confirm('¿Estás seguro de querer eliminar esta asignación?');
  }

       // Handle click on "Select all" control
    $('#example-select-all').on('click', function(){
      // Check/uncheck all checkboxes in the table
      var rows = table.rows({ 'search': 'applied' }).nodes();
      $('input[type="checkbox"]', rows).prop('checked', this.checked);
    });

   // Handle click on checkbox to set state of "Select all" control
   $('#assignments-table tbody').on('change', 'input[type="checkbox"]', function(){
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
</script>
@endpush
