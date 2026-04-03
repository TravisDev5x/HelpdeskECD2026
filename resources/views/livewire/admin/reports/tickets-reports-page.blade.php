<div wire:init="emitChartData" id="dash-reports-root">
    <div class="card card-outline card-primary mb-3">
        <div class="card-header d-flex align-items-center flex-wrap">
            <h3 class="card-title mb-0">Filtros de reporte</h3>
            <div class="card-tools ml-auto">
                <button type="button" class="btn btn-sm btn-outline-secondary dash-theme-toggle" title="Mismo modo oscuro que la barra superior (tema global)">
                    <i class="fas fa-moon dash-theme-icon-moon" aria-hidden="true"></i>
                    <i class="fas fa-sun dash-theme-icon-sun" aria-hidden="true"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form class="row" wire:submit.prevent="applyFilters">
                <div class="form-group col-md-3">
                    <label>Fecha inicio</label>
                    <input type="date" class="form-control @error('fechaInicio') is-invalid @enderror" wire:model="fechaInicio">
                    @error('fechaInicio')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group col-md-3">
                    <label>Fecha fin</label>
                    <input type="date" class="form-control @error('fechaFin') is-invalid @enderror" wire:model="fechaFin">
                    @error('fechaFin')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group col-md-3 align-self-end">
                    <button type="submit" class="btn btn-primary btn-sm btn-block">
                        <i class="fas fa-filter mr-1"></i> Aplicar filtros
                    </button>
                </div>
                <div class="form-group col-md-3 align-self-end">
                    <a href="{{ route('admin.reports.download', ['fechaInicio' => $fechaInicio, 'fechaFin' => $fechaFin]) }}" class="btn btn-outline-success btn-sm btn-block">
                        <i class="fas fa-file-excel mr-1"></i> Descargar Excel
                    </a>
                </div>
                <div class="col-12 mt-2 d-flex flex-wrap align-items-center">
                    <span class="text-muted small mr-2">Atajos:</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary mr-1 mb-1" data-preset="today">Hoy</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary mr-1 mb-1" data-preset="last7">Últimos 7 días</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary mb-1" data-preset="month">Mes actual</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalTickets }}</h3>
                    <p>Tickets totales</p>
                </div>
                <div class="icon"><i class="fas fa-ticket-alt"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $totalFinalizados }}</h3>
                    <p>Tickets cerrados</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $totalPendientes }}</h3>
                    <p>Tickets pendientes</p>
                </div>
                <div class="icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $totalIncidencias }}</h3>
                    <p>Incidencias</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </div>
    </div>

    <div class="row align-items-stretch">
        <div class="col-12 col-md-6 col-xl-4 mb-4">
            <div class="card card-outline card-primary h-100">
                <div class="card-header py-2">
                    <h3 class="card-title mb-0">Tickets por estatus</h3>
                </div>
                <div class="card-body pt-2"><div id="lw-chart-status"></div></div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4 mb-4">
            <div class="card card-outline card-secondary h-100">
                <div class="card-header py-2">
                    <h3 class="card-title mb-0">Tickets por área</h3>
                </div>
                <div class="card-body pt-2"><div id="lw-chart-areas"></div></div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4 mb-4">
            <div class="card card-outline card-info h-100">
                <div class="card-header py-2">
                    <h3 class="card-title mb-0">Top fallas (tickets)</h3>
                </div>
                <div class="card-body pt-2"><div id="lw-chart-failures"></div></div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4 mb-4">
            <div class="card card-outline card-danger h-100">
                <div class="card-header py-2">
                    <h3 class="card-title mb-0">Incidencias por criticidad</h3>
                </div>
                <div class="card-body pt-2"><div id="lw-chart-incidencias"></div></div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4 mb-4">
            <div class="card card-outline card-warning h-100">
                <div class="card-header py-2">
                    <h3 class="card-title mb-0">Tickets por usuario</h3>
                </div>
                <div class="card-body pt-2"><div id="lw-chart-users"></div></div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4 mb-4">
            <div class="card card-outline card-success h-100">
                <div class="card-header py-2">
                    <h3 class="card-title mb-0">Tickets por responsable</h3>
                </div>
                <div class="card-body pt-2"><div id="lw-chart-users-solution"></div></div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4 mb-4">
            <div class="card card-outline card-info h-100">
                <div class="card-header py-2">
                    <h3 class="card-title mb-0">Tiempo de atención por área (hrs)</h3>
                </div>
                <div class="card-body pt-2"><div id="lw-chart-time"></div></div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4 mb-4">
            <div class="card card-outline card-secondary h-100">
                <div class="card-header py-2">
                    <h3 class="card-title mb-0">Incidencias por sistema</h3>
                </div>
                <div class="card-body pt-2"><div id="lw-chart-incidencias-sistemas"></div></div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4 mb-4">
            <div class="card card-outline card-primary h-100">
                <div class="card-header py-2">
                    <h3 class="card-title mb-0">Incidencias por sede de soporte</h3>
                </div>
                <div class="card-body pt-2"><div id="lw-chart-incidencias-sede"></div></div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4 mb-4">
            <div class="card card-outline card-danger h-100">
                <div class="card-header py-2">
                    <h3 class="card-title mb-0">Disponibilidad por sistema (%)</h3>
                </div>
                <div class="card-body pt-2"><div id="lw-chart-time-incidencia"></div></div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            if (window.__reportsDashboardChartBound) return;
            window.__reportsDashboardChartBound = true;

            var COLUMN_HEIGHT = 360;
            var PIE_HEIGHT = 400;

            function isDarkTheme() {
                var html = document.documentElement;
                var body = document.body;
                if (html.getAttribute('data-theme') === 'dark') {
                    return true;
                }
                if (html.classList.contains('dark-mode')) {
                    return true;
                }
                if (body && body.classList.contains('dark-mode')) {
                    return true;
                }
                return false;
            }

            var themeRepaintTimer = null;

            function repaintIfPayload() {
                if (!window.__lastReportsPayload) {
                    return;
                }
                window.clearTimeout(themeRepaintTimer);
                themeRepaintTimer = window.setTimeout(function () {
                    paintAll(window.__lastReportsPayload);
                    window.setTimeout(reflowDashboardCharts, 80);
                }, 50);
            }

            function observeGlobalTheme() {
                var body = document.body;
                var html = document.documentElement;
                if (!body || !window.MutationObserver) {
                    return;
                }
                var obs = new MutationObserver(function () {
                    repaintIfPayload();
                });
                obs.observe(body, { attributes: true, attributeFilter: ['class'] });
                obs.observe(html, { attributes: true, attributeFilter: ['class', 'data-theme'] });
            }

            observeGlobalTheme();

            function getChartTheme() {
                if (isDarkTheme()) {
                    return {
                        titleColor: '#e8eef7',
                        subtitleColor: '#9aa8bc',
                        axisLabelColor: '#a8b4c8',
                        axisTitleColor: '#8a9bb0',
                        gridLineColor: '#2f3d52',
                        lineColor: '#3d4f68',
                        tooltipBg: '#1a2330',
                        tooltipBorder: '#3d4f68',
                        tooltipText: '#e8eef7',
                        emptySlice: '#4a5568',
                        dataLabelColor: '#dce4f0'
                    };
                }
                return {
                    titleColor: '#243040',
                    subtitleColor: '#6c7b8a',
                    axisLabelColor: '#5d6c7a',
                    axisTitleColor: '#6c7b8a',
                    gridLineColor: '#e6ebf2',
                    lineColor: '#cfd8e6',
                    tooltipBg: '#ffffff',
                    tooltipBorder: '#d4dce6',
                    tooltipText: '#243040',
                    emptySlice: '#ced4da',
                    dataLabelColor: '#243040'
                };
            }

            function hasData(payload) {
                return payload && Array.isArray(payload.series) && payload.series.some(function (v) {
                    return Number(v) > 0;
                });
            }

            function columnBottomMargin(categoryCount) {
                var n = categoryCount || 1;
                return Math.min(130, 52 + Math.min(n, 12) * 8);
            }

            function renderColumn(containerId, title, payload, color) {
                var t = getChartTheme();
                var categories = (payload && payload.categories) ? payload.categories : [];
                var series = (payload && payload.series) ? payload.series : [];
                if (!hasData(payload)) {
                    categories = ['Sin datos'];
                    series = [0];
                }
                var marginBottom = columnBottomMargin(categories.length);
                Highcharts.chart(containerId, {
                    chart: {
                        type: 'column',
                        backgroundColor: 'transparent',
                        height: COLUMN_HEIGHT,
                        marginBottom: marginBottom,
                        spacing: [12, 12, 18, 12]
                    },
                    title: { text: null },
                    xAxis: {
                        categories: categories,
                        title: { text: null },
                        labels: {
                            autoRotation: [-45, -90],
                            style: { fontSize: '11px', color: t.axisLabelColor },
                            padding: 6
                        },
                        gridLineColor: t.gridLineColor,
                        lineColor: t.lineColor,
                        tickColor: t.lineColor
                    },
                    yAxis: {
                        min: 0,
                        title: { text: 'Total', align: 'high', style: { color: t.axisTitleColor } },
                        labels: { style: { color: t.axisLabelColor } },
                        gridLineColor: t.gridLineColor
                    },
                    legend: { enabled: false },
                    credits: { enabled: false },
                    tooltip: {
                        backgroundColor: t.tooltipBg,
                        borderColor: t.tooltipBorder,
                        style: { color: t.tooltipText }
                    },
                    series: [{ name: title || 'Total', data: series, color: color }]
                });
            }

            function renderDoughnut(containerId, title, payload, color) {
                var t = getChartTheme();
                var categories = (payload && payload.categories) ? payload.categories : [];
                var series = (payload && payload.series) ? payload.series : [];
                var data = categories.map(function (name, i) {
                    return { name: name, y: Number(series[i] || 0) };
                });

                if (!data.length || !hasData(payload)) {
                    data = [{ name: 'Sin datos', y: 1, color: t.emptySlice }];
                }

                Highcharts.chart(containerId, {
                    chart: {
                        type: 'pie',
                        backgroundColor: 'transparent',
                        height: PIE_HEIGHT,
                        spacing: [14, 14, 14, 14]
                    },
                    title: { text: null },
                    credits: { enabled: false },
                    tooltip: {
                        pointFormat: '<b>{point.y}</b>',
                        backgroundColor: t.tooltipBg,
                        borderColor: t.tooltipBorder,
                        style: { color: t.tooltipText }
                    },
                    plotOptions: {
                        pie: {
                            innerSize: '55%',
                            size: '72%',
                            center: ['50%', '50%'],
                            allowPointSelect: true,
                            cursor: 'pointer',
                            borderColor: isDarkTheme() ? '#1a2330' : '#ffffff',
                            dataLabels: {
                                enabled: true,
                                distance: 22,
                                connectorPadding: 4,
                                crop: false,
                                format: '{point.name}: {point.y}',
                                style: { color: t.dataLabelColor, textOutline: 'none', fontWeight: '500', fontSize: '11px' }
                            }
                        }
                    },
                    series: [{
                        name: title || 'Total',
                        colorByPoint: true,
                        colors: [color, '#6f42c1', '#17a2b8', '#fd7e14', '#20c997', '#dc3545', '#6c757d'],
                        data: data
                    }]
                });
            }

            function paintAll(payload) {
                if (!payload) return;
                window.__lastReportsPayload = payload;
                renderDoughnut('lw-chart-status', 'Tickets por estatus', payload.status, '#17a2b8');
                renderColumn('lw-chart-areas', 'Tickets por área', payload.areas, '#6f42c1');
                renderColumn('lw-chart-failures', 'Top fallas', payload.failures, '#007bff');
                renderDoughnut('lw-chart-incidencias', 'Incidencias por criticidad', payload.incidencias, '#dc3545');
                renderColumn('lw-chart-users', 'Tickets por usuario', payload.users, '#fd7e14');
                renderColumn('lw-chart-users-solution', 'Tickets por responsable', payload.usersSolution, '#28a745');
                renderColumn('lw-chart-time', 'Tiempo de atención por área (hrs)', payload.time, '#20c997');
                renderDoughnut('lw-chart-incidencias-sistemas', 'Incidencias por sistema', payload.incidenciasSistemas, '#6c757d');
                renderDoughnut('lw-chart-incidencias-sede', 'Incidencias por sede de soporte', payload.incidenciasSede, '#007bff');
                renderColumn('lw-chart-time-incidencia', 'Disponibilidad por sistema (%)', payload.timeIncidencia, '#e83e8c');
            }

            function reflowDashboardCharts() {
                if (typeof Highcharts === 'undefined' || !Highcharts.charts) {
                    return;
                }
                Highcharts.charts.forEach(function (chart) {
                    if (!chart || !chart.renderTo || !chart.renderTo.id) {
                        return;
                    }
                    if (String(chart.renderTo.id).indexOf('lw-chart-') !== 0) {
                        return;
                    }
                    var el = chart.renderTo;
                    var cardBody = el.closest('.card-body');
                    var w = Math.max(
                        cardBody ? cardBody.clientWidth - 8 : 0,
                        el.parentNode ? el.parentNode.clientWidth : 0,
                        el.clientWidth,
                        280
                    );
                    var series0 = chart.series && chart.series[0];
                    var isPie = series0 && series0.type === 'pie';
                    var h = isPie ? PIE_HEIGHT : COLUMN_HEIGHT;
                    chart.setSize(w, h, false);
                    chart.redraw(false);
                    chart.reflow();
                });
            }

            document.addEventListener('click', function (event) {
                var toggleBtn = event.target.closest('.dash-theme-toggle');
                if (!toggleBtn) return;
                event.preventDefault();
                var globalTrigger = document.getElementById('darkModeTrigger');
                if (globalTrigger) {
                    globalTrigger.click();
                    return;
                }
                if (document.body) {
                    document.body.classList.toggle('dark-mode');
                    repaintIfPayload();
                }
            });

            window.addEventListener('resize', function () {
                setTimeout(reflowDashboardCharts, 80);
            });

            document.addEventListener('livewire:init', function () {
                window.addEventListener('reports-dashboard-update', function (event) {
                    paintAll(event.detail ? event.detail.payload : null);
                    setTimeout(reflowDashboardCharts, 100);
                    setTimeout(reflowDashboardCharts, 350);
                    setTimeout(reflowDashboardCharts, 700);
                });

                document.querySelectorAll('#dash-reports-root [data-preset]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var preset = btn.getAttribute('data-preset');
                        var today = new Date();
                        var start = new Date(today);
                        var end = new Date(today);

                        if (preset === 'last7') {
                            start.setDate(today.getDate() - 6);
                        } else if (preset === 'month') {
                            start = new Date(today.getFullYear(), today.getMonth(), 1);
                        }

                        var formatDate = function (d) {
                            var y = d.getFullYear();
                            var m = String(d.getMonth() + 1).padStart(2, '0');
                            var day = String(d.getDate()).padStart(2, '0');
                            return y + '-' + m + '-' + day;
                        };

                        var dateInputs = document.querySelectorAll('input[type="date"]');
                        if (dateInputs.length < 2) return;
                        dateInputs[0].value = formatDate(start);
                        dateInputs[1].value = formatDate(end);
                        dateInputs[0].dispatchEvent(new Event('input', { bubbles: true }));
                        dateInputs[1].dispatchEvent(new Event('input', { bubbles: true }));

                        var form = btn.closest('form');
                        if (!form) return;
                        form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
                    });
                });
            });
        })();
    </script>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/highcharts.css') }}">
    <style>
        /* Iconos sol/luna en el botón de tema (resto: estilos del admin / AdminLTE) */
        #dash-reports-root .dash-theme-toggle .dash-theme-icon-sun {
            display: none;
        }

        #dash-reports-root .dash-theme-toggle .dash-theme-icon-moon {
            display: inline-block;
        }

        body.dark-mode #dash-reports-root .dash-theme-toggle .dash-theme-icon-sun {
            display: inline-block;
        }

        body.dark-mode #dash-reports-root .dash-theme-toggle .dash-theme-icon-moon {
            display: none;
        }

        #dash-reports-root .card.card-outline > .card-body {
            overflow: visible;
        }

        #dash-reports-root .card-body > div[id^="lw-chart-"] {
            width: 100%;
            min-height: 400px;
            overflow: visible;
        }

        #dash-reports-root .highcharts-container {
            overflow: visible !important;
        }

        #dash-reports-root .highcharts-root {
            overflow: visible !important;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('highcharts/js/highcharts.js') }}"></script>
@endpush

