<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <link rel="icon" type="ico" href="{{ asset('favicon.ico') }}" />
  <title>{{ config('app.name') }} | Nueva contraseña</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

  <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('adminlte/css/adminlte.min.css') }}">

  <link href="{{ asset('css/background.css') }}" rel="stylesheet">
  <link href="{{ asset('css/login.css') }}" rel="stylesheet">
  <link href="{{ asset('css/accessibility.css') }}" rel="stylesheet">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">

  @livewireStyles

  <style>
    body {
        background-repeat: no-repeat !important;
        background-attachment: fixed !important;
        background-position: center center !important;
        background-size: cover !important;
    }
    .cursor-pointer { cursor: pointer; }
  </style>
</head>

<body class="hold-transition login-page">
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

  <script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('adminlte/js/adminlte.min.js') }}"></script>
  <script>
    function togglePwd(inputId, iconId) {
      var input = document.getElementById(inputId);
      var icon = document.getElementById(iconId);
      if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye"); icon.classList.add("fa-eye-slash");
      } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash"); icon.classList.add("fa-eye");
      }
    }
  </script>
  @livewireScripts
</body>
</html>
