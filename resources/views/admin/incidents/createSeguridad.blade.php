@extends('admin.layout')

@section('title', '| Crear nueva incidencia')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Nueva Incidencia') }}
                </div>
                <div class="card-body">
                    <form action="{{ route('guardar.seguridad') }}" method="post" class="needs-validation" novalidate>
                        <div class="form-row">
                            @csrf
                            <div class="form-group col-md-3">
                                <label>Fecha de incidencia</label>
                                <div class="input-group date" id="fecha_incidencia" data-target-input="nearest">
                                    <input type="datetime-local" name="fecha_incidencia"
                                        class="form-control datetimepicker-input" required
                                        data-target="#reservationdatetime">
                                </div>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Lugar de Incidencia</label>
                                <div class="input-group date" id="lugar_incidencia">
                                    <input type="text" name="lugar_incidencia"
                                        class="form-control" required>
                                </div>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="ctg_contenido_id">Categoria</label>
                                <select name="ctg_contenido_id" id="ctg_contenido_id" class="form-control" required>
                                    <option value="" selected disabled>Selecciona...</option>
                                    @forelse ($categorias as $item)
                                        <option value="{{ $item->id }}">{{ $item->contenido }}</option>
                                    @empty
                                        <option value="">Sin categorías</option>
                                    @endforelse
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="ctg_subcategoria_id">Subcategoria</label>
                                <select name="ctg_subcategoria_id" id="ctg_subcategoria_id" class="form-control" required
                                    disabled>
                                    <option value="" selected disabled>Selecciona una categoría primero</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            @csrf
                            <div class="form-group col-md-6">
                                <label for="comentario">Comentarios</label>
                                <textarea class="form-control" name="comentario" id="comentario" rows="2" required></textarea>
                            </div>
                            <div class="form-group col-3 mx-auto">
                                <label for="">&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">Guardar</button>
                            </div>
                            <div class="form-group col-3 mx-auto">
                                <label for="">&nbsp;</label>
                                <a href="{{ route('ciberseguridad.incidencias') }}"
                                    class="btn btn-danger btn-block">Cancelar</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/sistema/asset-create.js') }}"></script>
@endpush
