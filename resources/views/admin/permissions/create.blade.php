@extends('admin.layout')

@section('title', '| Crear nuevo permiso')

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">Permiso</div>

      <div class="card-body">
        <form action="{{ route('admin.permissions.store') }}" method="post">
            @csrf
            @include('admin.permissions.partials.form')
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
