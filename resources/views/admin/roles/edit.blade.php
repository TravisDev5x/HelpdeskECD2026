@extends('admin.layout')

@section('title', '| Editar rol')

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">Role</div>

      <div class="card-body">
        <form action="{{ route('admin.roles.update', $role->id) }}" method="post">
            @csrf
            @method('PUT')
            @include('admin.roles.partials.form')
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
