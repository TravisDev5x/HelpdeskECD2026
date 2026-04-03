<div class="container-fluid">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="card card-outline card-primary">
        <div class="card-header">
          <h3 class="card-title mb-0">Confirmar contraseña</h3>
        </div>
        <div class="card-body">
          <p class="text-muted">Confirma tu contraseña actual para continuar.</p>

          <form wire:submit="confirm">
            <div class="form-group">
              <label for="password">Contraseña</label>
              <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" wire:model="password" required autocomplete="current-password">
              @error('password')
              <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
              @enderror
            </div>

            <div class="d-flex flex-wrap align-items-center">
              <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="confirm">
                <span wire:loading wire:target="confirm" class="spinner-border spinner-border-sm mr-1" role="status"></span>
                Confirmar
              </button>
              @if (Route::has('password.request'))
                <a class="btn btn-link ml-2" href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
              @endif
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
