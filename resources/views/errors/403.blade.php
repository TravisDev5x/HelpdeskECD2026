@extends('admin.layout')

@section('title', 'No autorizado')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">No autorizado</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">No autorizado</li>
                </ol>
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card card-warning card-outline shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-lock fa-4x text-warning mb-3"></i>
                        <h3 class="card-title">Página no autorizada</h3>
                        <p class="text-muted mb-4">No tiene permiso para acceder a este recurso.</p>
                        <a href="{{ route('home') }}" class="btn btn-primary">
                            <i class="fas fa-home mr-1"></i> Regresar a Inicio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
