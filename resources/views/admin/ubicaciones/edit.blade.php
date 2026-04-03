@extends('admin.layout')

@section('title', '| Actualizar ubicacion')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Editar ubicacion') }}
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.ubicaciones.update', $ubicacion) }}" method="post" class="needs-validation"
                        novalidate>
                        <div class="form-row">
                            @csrf
                            @method('PUT')
                            <input hidden value="{{ $ubicacion->id }}" name="ubicacionId" >
                            <div class="form-group col-md-3">
                                <label for="ubicacion">Ubicacion</label>
                                <input type="text" name="ubicacion"
                                    class="form-control  @error('ubicacion') is-invalid @enderror"
                                    value="{{ old('ubicacion', $ubicacion->ubicacion) }}" 
                                    oninput="this.value = this.value.toUpperCase()"
                                    required>
                                @error('ubicacion')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-3">
                                <label for="sede">Sedes</label>
                                <select name="sede" id="sede" class="form-control">
                                    <option value="" selected disabled>Selecciona una sede...</option>
                                    @foreach ($sedes as $sede)
                                        <option value="{{ $sede->id }}" {{ old('id_sede', $ubicacion->id_sede) == $sede->id ? 'selected' : '' }}>{{ $sede->sede }}</option>
                                    @endforeach
                                </select>
                                @error('sede')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-2">
                                <label for="">&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">Actualizar ubicacion</button>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="">&nbsp;</label>
                                <a href="{{ route('admin.ubicaciones.index') }}" class="btn btn-danger btn-block">Cancelar</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
