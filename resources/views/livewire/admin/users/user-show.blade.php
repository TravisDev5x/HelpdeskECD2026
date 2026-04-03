@php
    $nombreCompleto = trim(implode(' ', array_filter([
        $user->name,
        $user->ap_paterno,
        $user->ap_materno,
    ])));
    $sedesTexto = $user->sedes->isNotEmpty()
        ? $user->sedes->pluck('sede')->implode(', ')
        : ($user->sede ?: '—');
    $avatarPath = $user->avatar
        ? asset('uploads/avatars/'.$user->avatar)
        : asset('uploads/avatars/default.png');
@endphp
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    <img class="profile-user-img img-fluid img-circle" src="{{ $avatarPath }}" alt="{{ $user->name }}">
                </div>

                <h3 class="profile-username text-center">{{ $nombreCompleto !== '' ? $nombreCompleto : $user->name }}</h3>

                <p class="text-center mb-3">
                    @forelse ($user->roles as $role)
                        <span class="badge badge-info mr-1 mb-1">{{ $role->name }}</span>
                    @empty
                        <span class="text-muted small">Sin rol asignado</span>
                    @endforelse
                </p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Nombre</b> <span class="float-right text-right">{{ $user->name ?: '—' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Apellido paterno</b> <span class="float-right text-right">{{ $user->ap_paterno ?: '—' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Apellido materno</b> <span class="float-right text-right">{{ $user->ap_materno ?: '—' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>No. empleado</b> <span class="float-right text-right">{{ $user->usuario }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Correo</b> <span class="float-right text-right">{{ $user->email ?: '—' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Teléfono</b> <span class="float-right text-right">{{ $user->phone ?: '—' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Departamento</b> <span class="float-right text-right">{{ $user->department?->name ?: '—' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Puesto</b> <span class="float-right text-right">{{ $user->position?->name ?: '—' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Área (puesto)</b> <span class="float-right text-right">{{ $user->position?->area ?: '—' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Área</b> <span class="float-right text-right">{{ $user->area?->name ?: '—' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Campaña</b> <span class="float-right text-right">{{ $user->campaign?->name ?: '—' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Sede(s)</b> <span class="float-right text-right text-wrap" style="max-width: 60%;">{{ $sedesTexto }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Registro</b> <span class="float-right text-right">{{ $user->created_at?->format('d/m/Y H:i') ?: '—' }}</span>
                    </li>
                </ul>

                @can('update user')
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-block"><b>Editar</b></a>
                @endcan
                <a href="{{ route('admin.users.index') }}" class="btn btn-default btn-block"><b>Volver al listado</b></a>
            </div>
        </div>
    </div>
</div>
