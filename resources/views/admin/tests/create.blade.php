@extends('admin.layout')

@section('title', '| Crear nueva prueva')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Nueva Prueba') }}
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.tests.store') }}" method="post" class="needs-validation" novalidate>
                        <div class="form-row">
                            @csrf
                            <div class="form-group col-md-6">
                                <label for="asset_id">Activo</label>
                                <select class="custom-select select2 @error('asset_id') is-invalid @enderror"
                                    name="asset_id" id="asset_id" required>
                                    <option value="" selected disabled>Seleccione un activo...</option>
                                    @foreach ($assets as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                                @error('asset_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="status">Status</label>
                                <select class="custom-select @error('status') is-invalid @enderror" name="status"
                                    id="status" required>
                                    <option value="" selected disabled>Seleccione un status...</option>
                                    <option value="Activo">Activo</option>
                                    <option value="Inactivo">Inactivo</option>
                                </select>
                                @error('status')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label>Fecha de la prueba</label>
                                <div class="input-group date" id="reservationdatetime" data-target-input="nearest">
                                    <input type="text" name="test_date"
                                        class="form-control datetimepicker-input @error('test_date') is-invalid @enderror"
                                        required data-target="#reservationdatetime">
                                    <div class="input-group-append" data-target="#reservationdatetime"
                                        data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                                @error('test_date')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                              <label for="nivel">Nivel de prueba</label>
                              <select class="custom-select @error('nivel') is-invalid @enderror" name="nivel"
                                    id="nivel" required>
                                    <option value="" selected disabled>Seleccione un nivel...</option>
                                    <option value="1">Nivel 1</option>
                                    <option value="2">Nivel 2</option>
                                </select>
                              @error('nivel')
                                  <span class="invalid-feedback" role="alert">
                                      <strong>{{ $message }}</strong>
                                  </span>
                              @enderror
                          </div>
                            <div class="form-group col-md-12">
                                <label for="observations">Observaciones</label>
                                <textarea class="form-control @error('observations') is-invalid @enderror"
                                    name="observations" id="observations" rows="4" {{ old('observations') }}
                                    required></textarea>
                                @error('observations')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-3">
                                <button type="submit" class="btn btn-primary btn-block">Guardar prueba</button>
                            </div>
                            <div class="form-group col-md-3">
                                <a href="{{ route('admin.tests.index') }}" class="btn btn-danger btn-block">Cancelar</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <!-- date picker -->
    <link rel="stylesheet"
        href="{{ asset('adminlte/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
    <style media="screen">
        .dropdown-menu {
            z-index: 10000 !important;
        }

    </style>
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/select2/css/select2.min.css') }}">
@endpush
@push('scripts')
    <script src="{{ asset('adminlte/plugins/moment/moment.min.js') }}"></script>
    <!-- date picker -->
    <script src="{{ asset('adminlte/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
    <script src="{{ asset('js/sistema/asset-create.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('adminlte/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        moment.defineLocale('es', null);
        //Date picker
        $('#reservationdatetime').datetimepicker({
            icons: {
                time: 'far fa-clock'
            },
            locale: 'es'
        });
        //Initialize Select2 Elements
        $('.select2').select2({
            tags: false
        });

    </script>
@endpush
