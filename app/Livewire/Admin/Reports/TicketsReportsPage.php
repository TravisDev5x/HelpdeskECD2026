<?php

namespace App\Livewire\Admin\Reports;

use App\Http\Controllers\Admin\ReportsController;
use App\Models\Incident;
use App\Models\Service;
use App\Support\Tickets\TicketStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin.reports-tickets')]
class TicketsReportsPage extends Component
{
    public string $fechaInicio = '';

    public string $fechaFin = '';

    public int $totalTickets = 0;

    public int $totalFinalizados = 0;

    public int $totalPendientes = 0;

    public int $totalIncidencias = 0;

    public function mount(): void
    {
        abort_unless(Auth::check() && Auth::user()->can('read reports ticket'), 403);

        $this->fechaInicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->fechaFin = Carbon::now()->format('Y-m-d');
    }

    protected function rules(): array
    {
        return [
            'fechaInicio' => 'required|date',
            'fechaFin' => 'required|date|after_or_equal:fechaInicio',
        ];
    }

    protected function reportRequest(): Request
    {
        return Request::create('/admin/reports', 'GET', [
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
        ]);
    }

    protected function refreshTotals(): void
    {
        $start = $this->fechaInicio . ' 00:00:00';
        $end = $this->fechaFin . ' 23:59:59';

        $this->totalTickets = Service::whereBetween('created_at', [$start, $end])->count();
        $this->totalFinalizados = Service::whereBetween('created_at', [$start, $end])
            ->where('status', TicketStatus::FINALIZADO)
            ->count();
        $this->totalPendientes = max(0, $this->totalTickets - $this->totalFinalizados);
        $this->totalIncidencias = Incident::whereBetween('created_at', [$start, $end])->count();
    }

    /**
     * @param  Collection|array<int, object>  $rows
     */
    protected function toChartPayload(Collection|array $rows): array
    {
        $rows = collect($rows);

        return [
            'categories' => $rows->pluck('name')->map(fn ($n) => (string) $n)->values()->all(),
            'series' => $rows->pluck('total')->map(fn ($v) => (float) $v)->values()->all(),
        ];
    }

    /** Mediana de horas por área (servicios finalizados). */
    protected function buildTimeChartPayload(): array
    {
        $rows = Service::query()
            ->join('failures', 'failures.id', '=', 'services.failure_id')
            ->join('areas', 'areas.id', '=', 'failures.area_id')
            ->selectRaw('areas.name')
            ->selectRaw('ROUND(TIMESTAMPDIFF(MINUTE, services.created_at, services.fecha_fin) / 60) as diferencia_hours')
            ->where('services.status', TicketStatus::FINALIZADO)
            ->whereBetween('services.created_at', [$this->fechaInicio . ' 00:00:00', $this->fechaFin . ' 23:59:59'])
            ->get();

        $byArea = $rows->groupBy('name');

        $aggregated = $byArea->map(function ($group, $areaName) {
            $hours = $group->pluck('diferencia_hours')->map(fn ($h) => (float) $h)->sort()->values()->all();
            $n = count($hours);
            if ($n === 0) {
                return ['name' => $areaName, 'total' => 0.0];
            }
            $idx = (int) floor(($n - 1) / 2);
            if ($n % 2 === 1) {
                $mediana = (float) $hours[$idx];
            } else {
                $mediana = ((float) $hours[$idx] + (float) $hours[$idx + 1]) / 2;
            }

            return ['name' => $areaName, 'total' => $mediana];
        })->values();

        return $this->toChartPayload($aggregated);
    }

    /**
     * @return array<string, array{categories: array<int, string>, series: array<int, float>}>
     */
    public function buildChartPayload(): array
    {
        $req = $this->reportRequest();
        $reports = app(ReportsController::class);

        $status = $this->toChartPayload($reports->getReportDay($req));
        $areas = $this->toChartPayload($reports->getReportArea($req));
        $failures = $this->toChartPayload($reports->getReportFailure($req));
        $incidencias = $this->toChartPayload($reports->getReportIncidencia($req));
        $users = $this->toChartPayload($reports->getReportUser($req));
        $usersSolution = $this->toChartPayload($reports->getReportUserSolution($req));
        $time = $this->buildTimeChartPayload();
        $incidenciasSistemas = $this->toChartPayload($reports->getReportIncidenciaSistemas($req));
        $incidenciasSede = $this->toChartPayload($reports->getReportSedeTicket($req));
        $timeIncidencia = $this->toChartPayload($reports->getReportTimeIncidencias($req));

        return compact(
            'status',
            'areas',
            'failures',
            'incidencias',
            'users',
            'usersSolution',
            'time',
            'incidenciasSistemas',
            'incidenciasSede',
            'timeIncidencia'
        );
    }

    public function emitChartData(): void
    {
        $this->validate();
        $this->refreshTotals();
        $payload = $this->buildChartPayload();
        $this->dispatch('reports-dashboard-update', payload: $payload);
    }

    public function applyFilters(): void
    {
        $this->emitChartData();
    }

    public function render()
    {
        if ($this->fechaInicio === '') {
            $this->fechaInicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        }
        if ($this->fechaFin === '') {
            $this->fechaFin = Carbon::now()->format('Y-m-d');
        }

        $this->refreshTotals();

        return view('livewire.admin.reports.tickets-reports-page');
    }
}
