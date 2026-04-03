{{--
    Texto de ayuda estándar para formularios.
    Uso: @include('partials.form-help', ['text' => 'Ej: número de serie del fabricante'])
    O con id para asociar a un control: @include('partials.form-help', ['text' => '...', 'id' => 'campo-help'])
--}}
@if(!empty($text))
<small class="form-text text-muted help-text" @if(!empty($id)) id="{{ $id }}" @endif>{{ $text }}</small>
@endif
