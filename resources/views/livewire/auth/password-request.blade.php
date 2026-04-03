<div class="lw-auth-page-root">
  <a href="#main-content" class="skip-to-content">Ir al formulario</a>
  <div class="login-box" id="main-content" style="max-width: 480px;" tabindex="-1">

    <div class="card text-white bg-dark">
      <div class="card-body">

        <img id="img-logo-nav" src="{{ asset('adminlte/img/logo.png') }}" alt="HelpDesk Logo" class="img-center" style="display: block; margin: 0 auto 20px auto;">

        <p class="small text-center mb-3"><a href="{{ route('login') }}" class="text-light"><i class="fas fa-arrow-left mr-1"></i> Volver al inicio de sesión</a></p>

        @if (session('status'))
          <div class="alert alert-success alert-dismissible small mb-3">
            {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
          </div>
        @endif

        @if (session('message'))
          <div class="alert alert-success alert-dismissible small mb-3">
            {{ session('message') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
          </div>
        @endif

        <h5 class="text-center border-bottom border-secondary pb-2 mb-3">Restablecer contraseña</h5>
        <p class="small text-muted mb-3">Introduce el <strong>correo</strong> con el que diste de alta tu cuenta. Te enviaremos un enlace para elegir una contraseña nueva.</p>

        <form wire:submit="sendResetLink">
          <div class="input-group mb-3">
            <input id="email-reset" type="email" placeholder="Correo electrónico" class="form-control @error('email') is-invalid @enderror" wire:model="email" required autocomplete="email" autofocus>
            <div class="input-group-append">
              <div class="input-group-text"><span class="fas fa-envelope"></span></div>
            </div>
            @error('email')
            <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
          </div>
          <button type="submit" class="btn btn-primary btn-block" wire:loading.attr="disabled" wire:target="sendResetLink">
            <span wire:loading wire:target="sendResetLink" class="spinner-border spinner-border-sm mr-2" role="status"></span>
            <span wire:loading.remove wire:target="sendResetLink">Enviar enlace para restablecer contraseña</span>
            <span wire:loading wire:target="sendResetLink">Enviando…</span>
          </button>
        </form>

        <hr class="bg-secondary my-4">

        <h5 class="text-center border-bottom border-secondary pb-2 mb-3">¿Sigue sin funcionar?</h5>
        <p class="small text-muted mb-3">Si no recibes el correo, no tienes email registrado u otro problema impide entrar, envía un aviso a <strong>Soporte</strong> (usuario o correo y motivo).</p>

        <form wire:submit="sendSupportAlert">
          <div class="input-group mb-2">
            <input type="text" placeholder="Usuario o correo" class="form-control form-control-sm @error('supportIdentifier') is-invalid @enderror" wire:model="supportIdentifier" required autocomplete="username">
            <div class="input-group-append">
              <button type="submit" class="btn btn-warning btn-sm" wire:loading.attr="disabled" wire:target="sendSupportAlert">Alertar a Soporte</button>
            </div>
            @error('supportIdentifier')
            <span class="text-danger small d-block w-100 mt-1"><strong>{{ $message }}</strong></span>
            @enderror
          </div>
          <textarea class="form-control form-control-sm @error('supportReason') is-invalid @enderror" rows="2" placeholder="Motivo (opcional)" wire:model="supportReason"></textarea>
          @error('supportReason')
          <span class="text-danger small d-block mt-1"><strong>{{ $message }}</strong></span>
          @enderror
        </form>

      </div>
    </div>
  </div>
</div>
