@extends('admin.layout')

@section('title', '| Actualizar campaña')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Editar Campaña') }}
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.campaigns.update', $campaign) }}" method="post" class="needs-validation"
                        novalidate>
                        <div class="form-row">
                            @csrf
                            @method('PUT')
                            <div class="form-group col-md-6">
                                <label for="name">Nombre</label>
                                <input type="text" name="name"
                                    class="form-control  @error('name') is-invalid @enderror"
                                    value="{{ old('name', $campaign->name) }}" required>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="did_id">DID</label>
                                <select class="custom-select" name="did_id" id="did_id">
                                    <option value="" selected disabled>Selecciona...</option>
                                    @foreach ($dids as $did)
                                        <option value="{{ $did->id }}"
                                            @if ($campaign->did_id == $did->id) selected @endif>{{ $did->did }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="">&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">Actualizar campaña</button>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="">&nbsp;</label>
                                <a href="{{ route('admin.campaigns.index') }}" class="btn btn-danger btn-block">Cancelar</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('js/sistema/campaigns-create.js') }}"></script>
@endpush
