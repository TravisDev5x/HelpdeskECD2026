@extends('admin.layout')

@section('title', '| Reporte de inventario')
@section('header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">REPORTE DETALLADO DE INVENTARIO
                    <!-- <small>Listado</small> -->
                </h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Reportes</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
@endsection
@section('content')
    <section class="content">
        <div class="container-fluid">
            @livewire('admin.reports.inventory-dashboard')
        </div>
    </section>
@endsection
