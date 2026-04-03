@extends('admin.layout')

@section('title', '| Detalle de asignación')
@section('header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">DETALLE
                    <!-- <small>Listado</small> -->
                </h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.assignments.list') }}">Lista de asignaciones</a>
                    </li>
                    <li class="breadcrumb-item active">Detalle de asignación por revisar</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">

                    {{-- <h3 class="profile-username text-left d-inline">{{ $user->name }}</h3>

          <p class="text-muted text-left d-inline">{{ $user->department->name }}</p> --}}

                    <table id="assignments-table" class="table table-bordered table-sm mt-4">
                        <thead>
                            <tr>
                                <th>Equipo</th>
                                <th>Marca</th>
                                <th>Modelo</th>
                                <th>Num serie</th>
                                <th>Etiqueta</th>
                                <th>Empresa</th>
                                <th>Revisión</th> 
                               
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($assignments as $assignment)
                           
                                <tr>
                                    <td>{{ $assignment->name }}</td>
                                    <td>{{ $assignment->marca }}</td>
                                    <td>{{ $assignment->modelo }}</td>
                                    <td>{{ $assignment->serie }}</td>
                                    <td>{{ $assignment->etiqueta }}</td>
                                    <td>{{ $assignment->company->name }}</td>  
                                    <td>
                                        
                                        @if ($assignment->revision == 1)
                                                           
                                            <input type="text" name="serie{{ $assignment->id }}" id='serie{{ $assignment->id }}' style="text-transform:uppercase;" placeholder="Revisado" disabled >

                                        @elseif(!is_null($assignment->review_observations))
                                             <input type="text" name="serie{{ $assignment->id }}" id='serie{{ $assignment->id }}' style="text-transform:uppercase;" placeholder="{{ $assignment->review_observations }}">
                                             
                                        @else  
                                        <input type="text" name="serie{{ $assignment->id }}" id='serie{{ $assignment->id }}' style="text-transform:uppercase;"> 

                                        @endif    
                                                   
                                        <input type="checkbox" id="{{ $assignment->id }}" name="chkbox{{ $assignment->id }}" onclick="revisado({{ $assignment->id }})" {{ $assignment->revision == 1 ? 'checked' : '' }} {{ $assignment->revision == 1 ? 'disabled' : '' }}>
                                    </td>                       
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>


    <!--modal-->
 @foreach ($assignments as $assignment)
  <div class="modal fade" id="myModal{{ $assignment->id }}" role="dialog">
      <div class="modal-dialog">
      
        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">Observaciones</h4>
          </div>
          <div class="modal-body">
            <p>Las numeros de serie no coinsiden, favor de escribir sus observaciones</p>
            <form action="{{ route('admin.revision.observation', $assignment->id) }}" method="POST">
                @csrf
            <textarea name="observations" class="form-control" rows="3"></textarea>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary" >Enviar</button>
            </form>
          </div>
        </div>
        
      </div>
    </div>
    @endforeach


@endsection
@push('scripts')
    <!-- DataTables -->
    <script src="{{ asset('adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <script>
       
        $(function() {

            if ($(window).width() < 576) {
                $('#assignments-table').removeClass('nowrap');
            } else {
                $('#assignments-table').addClass('nowrap');
            }

            var table = $('#assignments-table').DataTable({
                "processing": true,
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                language: {
                    'url': '../../js/spanish.json',
                },
                order: [
                    [0, 'asc']
                ],
                columnDefs: [{
                        orderable: false,
                        targets: [3]
                    },
                    {
                        className: "text-center",
                        targets: [3]
                    },
                ],
            });
        });
           

         function revisado(id) {
            var serie;
            serie = $('#serie'+id).val();
           
            console.log(serie);
            $.ajax({
                url: "{{ route('admin.revisionAuditor') }}",
                data: {
                    id: id,
                    serie: serie,
                },
                beforeSend: function() {
                    // alert('before send');
                }
            }).done(function(response) {
                //location.reload();
                //console.log(response);
                if (response) {
                       $( document ).ready(function() {
                        $('#myModal'+response).modal('toggle')
                    });
                }else{
                    alert('Registro correcto');
                }
            });


        }


    </script>
@endpush
