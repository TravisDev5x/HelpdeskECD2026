@extends('admin.layout')

@section('content')
<div class="container-fluid">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      @if (session('error'))
        <div class="alert alert-warning">{{ session('error') }}</div>
      @endif
      <div class="card card-warning card-outline">
        <div class="card-header">
          <h3 class="card-title mb-0">Cambiar contraseña</h3>
        </div>
        <div class="card-body">
          <p class="text-muted">Tu contraseña ha caducado o debes actualizarla. Elige una contraseña nueva.</p>
          <form method="POST" action="{{ route('password.change.submit') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="form-group">
              <label for="email">Correo</label>
              <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $email) }}" required autocomplete="email" @if(!empty($email)) readonly @endif>
              @error('email')
              <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
              @enderror
            </div>
            <div class="form-group">
              <label for="password">Nueva contraseña</label>
              <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
              @error('password')
              <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
              @enderror
            </div>
            <div class="form-group">
              <label for="password_confirmation">Confirmar contraseña</label>
              <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-warning">Actualizar contraseña</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
