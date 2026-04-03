@extends('admin.layout')

@section('title', '| Permisos')

@section('header')
<div class="container-fluid">
  <div class="row mb-2">
    <div class="col-sm-6">
      <h1 class="m-0 text-dark">PERMISOS</h1>
    </div>
    <div class="col-sm-6">
      <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Permisos</li>
      </ol>
    </div>
  </div>
</div>
@endsection

@section('content')
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
          <span>Lista de permisos (agrupados para lectura; el nombre técnico es el que usa el código)</span>
          <small class="text-muted">Catálogo: <code>config/permission_catalog.php</code></small>
        </div>

        <div class="card-body">
          <div class="row justify-content-end pb-2">
            @can('create permission')
            <a href="{{ route('admin.permissions.create') }}" class="btn btn-sm btn-primary"><i class="fa fa-plus"></i> Crear un permiso</a>
            @endcan
          </div>
          <div class="table-responsive">
            <table class="table table-striped table-sm table-bordered">
              <thead class="thead-light">
                <tr>
                  <th style="width:50px;">ID</th>
                  <th style="min-width:200px;">Módulo / área</th>
                  <th style="min-width:220px;">Etiqueta (referencia)</th>
                  <th>Nombre técnico</th>
                  <th style="min-width:260px;">Descripción</th>
                  <th style="width:130px;" class="text-center">Acciones</th>
                </tr>
              </thead>
              <tbody>
              @foreach ($permissionRows as $row)
              <tr>
                <td>{{ $row['id'] }}</td>
                <td><span class="badge badge-secondary">{{ $row['group_label'] }}</span></td>
                <td>{{ $row['label'] }}</td>
                <td><code class="small">{{ $row['name'] }}</code></td>
                <td class="small text-muted">{{ $row['description'] ?? '—' }}</td>
                <td>
                  <div class="d-flex flex-wrap justify-content-center">
                    @can('update permission')
                    <a href="{{ route('admin.permissions.edit', $row['id']) }}" class="btn btn-primary btn-sm mr-1">Editar</a>
                    @endcan
                    @can('delete permission')
                    <div class="d-inline">@include('admin.permissions.delete', ['permission' => $row['permission']])</div>
                    @endcan
                  </div>
                </td>
              </tr>
              @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
