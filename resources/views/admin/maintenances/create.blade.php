@extends('admin.layout')

@section('title', '| Crear nuevo mantenimiento')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Nuevo Mantenimiento') }}
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Serie:</label> <span>{{ $product->serie }}</span>
                        </div>
                        <div class="col-md-3">
                            <label>Producto:</label> <span>{{ $product->name }}</span>
                        </div>
                        <div class="col-md-3">
                            <label>Marca:</label> <span>{{ $product->marca }}</span>
                        </div>
                        <div class="col-md-3">
                            <label>Modelo:</label> <span>{{ $product->modelo }}</span>
                        </div>
                        <div class="col-md-3">
                            <label>Ultimo mantenimiento:</label> <span>{{ $product->maintenance_date }}</span>
                        </div>
                        <div class="col-md-9">
                            <label>Observaciones:</label> <span>{{ $product->maintenance }}</span>
                        </div>

                    </div>
                    <form action="{{ route('admin.maintenances.update', $product) }}" method="post"
                        class="needs-validation" enctype="multipart/form-data" novalidate>
                        <div class="form-row">
                            @csrf
                            @method('PUT')
                            <div class="form-group col-md-6">
                                <label>Fecha de mantenimiento</label>
                                <div class="input-group" data-target-input="nearest">
                                    <input type="date" value="{{ old('maintenance_date') }}" name="maintenance_date"
                                        class="form-control  @error('maintenance_date') is-invalid @enderror" required
                                        id="datepicker1" />
                                    @error('maintenance_date')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="maintenance">Observaciones</label>
                                <textarea class="form-control @error('maintenance') is-invalid @enderror" name="maintenance" id="maintenance"
                                    rows="3" {{ old('maintenance') }} required></textarea>
                                @error('maintenance')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="pdf_mantenimiento">Archivo Evidencias:</label>
                                <input type="file" name="pdf_mantenimiento" id="pdf_mantenimiento"
                                    class="@error('pdf_mantenimiento') is-invalid @enderror" accept=".pdf" />
                                @error('pdf_mantenimiento')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <button type="submit" class="btn btn-primary btn-block">Guardar mantenimiento</button>
                                <a href="{{ route('admin.products.index') }}" class="btn btn-danger btn-block">Cancelar</a>
                            </div>
                        </div>
                    </form>
                    <div class="row">
                        <div class="col-md-12">
                            <hr>
                            <label>Historico</label>
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Observaciones</th>
                                        <th>Reponsable</th>
                                        <th>Documento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($maintenances as $maintenance)
                                        <tr>
                                            <td>{{ $maintenance->maintenance_date }}</td>
                                            <td>{{ $maintenance->maintenance }}</td>
                                            <td>{{ $maintenance->user->name }}</td>
                                            <td>
                                                @if ($maintenance->pdf_mantenimiento)
                                                    {{-- {{ $maintenance->pdf_mantenimiento }} --}}
                                                    <a href="{{ asset('storage_celer2/helpdesk/mantenimientos/' . $maintenance->pdf_mantenimiento) }}"
                                                      target="_blank">Ver PDF</a>
                                                @else
                                                    {{ 'SIN EVIDENCIA' }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('styles')
    <!-- date picker -->
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/datepiker/css/bootstrap-datepicker.min.css') }}">
    <style media="screen">
        .dropdown-menu {
            z-index: 10000 !important;
        }
    </style>
@endpush
@push('scripts')
    <!-- date picker -->
    <script src="{{ asset('adminlte/plugins/datepiker/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('js/sistema/product-create.js') }}"></script>

    <script>
        moment.defineLocale('es', null);
        //Date picker
        $('#datepicker').datepicker({
            autoclose: true,
            locale: 'es',
            format: 'yyyy/mm/dd'
        });
    </script>
@endpush
