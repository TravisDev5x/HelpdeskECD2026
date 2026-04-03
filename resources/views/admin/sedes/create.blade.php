@extends('admin.layout')

@section('title', '| Crear nueva sede')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Nueva Sede') }}
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.sedes.store') }}" method="post" class="needs-validation" novalidate>
                        <div class="form-row">
                            @csrf
                            <div class="form-group col-md-6">
                                <label for="sede">Sede</label>
                                <input type="text" name="sede"
                                    class="form-control  @error('sede') is-invalid @enderror" value="{{ old('sede') }}"
                                    oninput="this.value = this.value.toUpperCase()"
                                    required>
                                @error('sede')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-3">
                                <label for="">&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">Guardar sede</button>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="">&nbsp;</label>
                                <a href="{{ route('admin.sedes.index') }}" class="btn btn-danger btn-block">Cancelar</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
