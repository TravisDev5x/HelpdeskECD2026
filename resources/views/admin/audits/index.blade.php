@extends('admin.layout')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <h1><i class="fas fa-history"></i> Trazabilidad del Sistema</h1>
        </div>
    </section>

    <section class="content">
        {{-- Aquí se inyecta la tabla de Livewire --}}
        <livewire:admin.audit-table />
    </section>
@endsection