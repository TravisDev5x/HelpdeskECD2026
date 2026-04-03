{{--
    Breadcrumb coherente para toda la app.
    Uso: @include('partials.breadcrumb', ['items' => [['text' => 'Inicio', 'url' => route('home')], ['text' => 'Inventario V2', 'url' => route('inventory.v2.index')], ['text' => 'Listado de Activos', 'url' => null]]])
    El último ítem con url null se muestra como activo.
--}}
<div class="content-header mb-2">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ end($items)['text'] ?? 'Inicio' }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    @foreach ($items as $item)
                        @if (!empty($item['url']))
                            <li class="breadcrumb-item"><a href="{{ $item['url'] }}">{{ $item['text'] }}</a></li>
                        @else
                            <li class="breadcrumb-item active">{{ $item['text'] }}</li>
                        @endif
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
</div>
