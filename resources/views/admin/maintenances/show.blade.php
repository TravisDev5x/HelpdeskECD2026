@extends('admin.layout')

@section('title', '| Detalle de mantenimiento')

@section('content')
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">{{ __('Nuevo Mantenimiento') }}
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-3">
              <label>Serie:</label> <span>{{ $product->serie }}</span>
            </div>
            <div class="col-md-3">
              <label>Producto:</label> <span>{{ $product->name }}</span>
            </div>
            <div class="col-md-3">
              <label>Marca:</label> <span>{{ $product->marca }}</span>
            </div>
            <div class="col-md-3">
              <label>Modelo:</label> <span>{{ $product->modelo }}</span>
            </div>
            <div class="col-md-3">
              <label>Ultimo mantenimiento:</label> <span>{{ $product->maintenance_date }}</span>
            </div>
            <div class="col-md-9">
              <label>Observaciones:</label> <span>{{ $product->maintenance }}</span>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <hr>
              <label>Historico</label>
              <table class="table table-bordered table-sm">
                <thead>
                  <tr>
                    <th>Fecha</th>
                    <th>Observaciones</th>
                    <th>Reponsable</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($maintenances as $maintenance)
                    <tr>
                      <td>{{ $maintenance->maintenance_date }}</td>
                      <td>{{ $maintenance->maintenance }}</td>
                      <td>{{ $maintenance->user->name }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
