<div class="row justify-content-center">
    
    {{-- 1. Generados --}}
    <div class="col-lg-2 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $generados }}</h3>
                <p>Generados</p>
            </div>
            <div class="icon">
                <i class="fas fa-pen"></i>
            </div>
            <a href="#" class="small-box-footer">
                &nbsp;
            </a>
        </div>
    </div>

    {{-- 2. Pendientes --}}
    <div class="col-lg-2 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $pendientes }}</h3>
                <p>Pendientes</p>
            </div>
            <div class="icon">
                <i class="far fa-clock"></i>
            </div>
            <a href="#" class="small-box-footer">
                &nbsp;
            </a>
        </div>
    </div>

    {{-- 3. En Proceso --}}
    <div class="col-lg-2 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $seguimientos }}</h3>
                <p>En proceso</p>
            </div>
            <div class="icon">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <a href="#" class="small-box-footer">
                &nbsp;
            </a>
        </div>
    </div>

    {{-- 4. Finalizados --}}
    <div class="col-lg-2 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $finalizados }}</h3>
                <p>Finalizados</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-double"></i>
            </div>
            <a href="#" class="small-box-footer">
                &nbsp;
            </a>
        </div>
    </div>

    {{-- 5. Ticket Erróneo --}}
    {{-- Nota: Usé bg-maroon para distinguirlo del rojo de 'Pendientes', pero es opcional --}}
    <div class="col-lg-2 col-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ $ticketMal }}</h3>
                <p>Ticket Erróneo</p>
            </div>
            <div class="icon">
                <i class="fas fa-times"></i>
            </div>
            <a href="#" class="small-box-footer">
                &nbsp;
            </a>
        </div>
    </div>

</div>