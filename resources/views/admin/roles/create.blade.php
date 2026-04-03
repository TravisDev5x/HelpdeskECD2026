@extends('admin.layout')

@section('title', '| Crear nuevo roles')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Role</div>

                <div class="card-body">
                    <form action="{{ route('admin.roles.store') }}" method="post">
                        @csrf
                        @include('admin.roles.partials.form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
