@extends('admin.layout')

@section('title', '| Editar permiso')

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">Permiso</div>

      <div class="card-body">
        <form action="{{ route('admin.permissions.update', $permission->id) }}" method="post">
            @csrf
            @method('PUT')
            @include('admin.permissions.partials.form')
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
