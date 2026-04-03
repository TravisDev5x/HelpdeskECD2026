<div class="lw-auth-page-root">
  <a href="#main-content" class="skip-to-content">Ir al formulario de acceso</a>
  <div class="login-box" id="main-content" tabindex="-1">

    <div class="card text-white bg-dark">
      <div class="card-body">

        <img id="img-logo-nav" src="{{ asset('adminlte/img/logo.png') }}" alt="HelpDesk Logo" class="img-center" style="display: block; margin: 0 auto 20px auto;">

        @if (session('error'))
          <div class="alert alert-danger alert-dismissible small mb-3">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
          </div>
        @endif
        @if (session('message'))
          <div class="alert alert-success alert-dismissible small mb-3">
            {{ session('message') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
          </div>
        @endif
        <form id="loginForm" wire:submit="authenticate">
          <div class="input-group mb-3">
            <input id="usuario" type="text" placeholder="Usuario o correo" class="form-control @error('usuario') is-invalid @enderror" name="usuario" wire:model="usuario" required autocomplete="username" autofocus>

            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-user"></span>
              </div>
            </div>

            @error('usuario')
            <span class="invalid-feedback" role="alert">
              <strong>{{ $message }}</strong>
            </span>
            @enderror
          </div>

          <div class="input-group mb-3">
            <input id="password" type="password" placeholder="Contraseña" class="form-control @error('password') is-invalid @enderror" name="password" wire:model="password" required autocomplete="current-password">

            <div class="input-group-append">
              <div class="input-group-text cursor-pointer" onclick="togglePassword()">
                <span id="toggleIcon" class="fas fa-eye"></span>
              </div>
            </div>

            @error('password')
            <span class="invalid-feedback" role="alert">
              <strong>{{ $message }}</strong>
            </span>
            @enderror
          </div>

          <div class="row">
            <div class="col-12">
              <button type="submit" class="btn btn-primary btn-block" id="btn-login" wire:loading.attr="disabled" wire:target="authenticate">
                <span wire:loading wire:target="authenticate" class="spinner-border spinner-border-sm mr-2" role="status"></span>
                <span id="btn-text"><span wire:loading.remove wire:target="authenticate">Entrar</span><span wire:loading wire:target="authenticate">Entrando...</span></span>
              </button>
            </div>
          </div>
        </form>

        <p class="small text-muted mb-0 text-center"><a href="{{ route('password.request') }}" class="text-light">¿Olvidaste tu contraseña o no puedes entrar?</a></p>

      </div>
    </div>
  </div>
</div>

@push('guest-scripts')
<script>
  function togglePassword() {
    var input = document.getElementById('password');
    var icon = document.getElementById('toggleIcon');
    if (!input || !icon) return;
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.remove('fa-eye');
      icon.classList.add('fa-eye-slash');
    } else {
      input.type = 'password';
      icon.classList.remove('fa-eye-slash');
      icon.classList.add('fa-eye');
    }
  }
</script>
@endpush
