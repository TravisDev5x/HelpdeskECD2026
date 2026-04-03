<div class="lw-auth-page-root">
  <a href="#main-content" class="skip-to-content">Ir al formulario</a>
  <div class="login-box" id="main-content" style="max-width: 480px;" tabindex="-1">

    <div class="card text-white bg-dark">
      <div class="card-body">

        <img id="img-logo-nav" src="{{ asset('adminlte/img/logo.png') }}" alt="HelpDesk Logo" class="img-center" style="display: block; margin: 0 auto 20px auto;">

        <p class="small text-center mb-3"><a href="{{ route('login') }}" class="text-light"><i class="fas fa-arrow-left mr-1"></i> Volver al inicio de sesión</a></p>

        <h5 class="text-center border-bottom border-secondary pb-2 mb-3">Elegir contraseña nueva</h5>

        <form wire:submit="resetPassword">
          <div class="input-group mb-3">
            <input id="email" type="email" placeholder="Correo electrónico" class="form-control @error('email') is-invalid @enderror" wire:model="email" required autocomplete="email" autofocus>
            <div class="input-group-append">
              <div class="input-group-text"><span class="fas fa-envelope"></span></div>
            </div>
            @error('email')
            <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
          </div>

          <div class="input-group mb-3">
            <input id="password" type="password" placeholder="Contraseña nueva" class="form-control @error('password') is-invalid @enderror" wire:model="password" required autocomplete="new-password">
            <div class="input-group-append">
              <div class="input-group-text cursor-pointer" onclick="togglePwd('password','icon-pw')"><span id="icon-pw" class="fas fa-eye"></span></div>
            </div>
            @error('password')
            <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
          </div>

          <div class="input-group mb-3">
            <input id="password_confirmation" type="password" placeholder="Confirmar contraseña" class="form-control" wire:model="password_confirmation" required autocomplete="new-password">
            <div class="input-group-append">
              <div class="input-group-text cursor-pointer" onclick="togglePwd('password_confirmation','icon-pw2')"><span id="icon-pw2" class="fas fa-eye"></span></div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-block" wire:loading.attr="disabled" wire:target="resetPassword">
            <span wire:loading wire:target="resetPassword" class="spinner-border spinner-border-sm mr-2" role="status"></span>
            <span wire:loading.remove wire:target="resetPassword">Restablecer contraseña</span>
            <span wire:loading wire:target="resetPassword">Guardando…</span>
          </button>
        </form>

      </div>
    </div>
  </div>
</div>
