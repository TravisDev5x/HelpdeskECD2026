<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Calendar;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:read calendar']);
    }

    public function index()
    {
        return view('admin.calendar.index', [
            'canReadTeamCalendar' => $this->userCanReadTeam(),
            'canManageTeamCalendar' => $this->userCanManageTeam(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'actividad' => 'required|string|max:500',
            'descripcion' => 'nullable|string|max:5000',
            'status' => 'required|string|max:32',
            'date_post' => 'required|date',
            'date_end' => 'nullable|date',
            'time' => 'nullable|date_format:H:i',
            'scope' => 'nullable|in:'.Calendar::SCOPE_PERSONAL.','.Calendar::SCOPE_TEAM,
        ]);

        $scope = $validated['scope'] ?? Calendar::SCOPE_PERSONAL;
        if ($scope === Calendar::SCOPE_TEAM) {
            abort_unless($this->userCanManageTeam(), 403);
        }

        Calendar::create([
            'user_id' => $request->user()->id,
            'scope' => $scope === Calendar::SCOPE_TEAM ? Calendar::SCOPE_TEAM : Calendar::SCOPE_PERSONAL,
            'actividad' => $request->actividad,
            'descripcion' => $request->descripcion,
            'status' => $request->status,
            'start_date' => $request->date_post,
            'hora' => $request->time,
            'end_date' => $request->date_end,
        ]);

        return back();
    }

    public function update(Request $request)
    {
        $request->validate([
            'actividad_update' => 'required|string|max:500',
            'descripcion_update' => 'nullable|string|max:5000',
            'status_update' => 'required|string|max:32',
            'date_post_update' => 'required|date',
            'date_end_update' => 'nullable|date',
            'time_update' => 'nullable|date_format:H:i',
            'id_update' => 'required|integer|exists:calendars,id',
        ]);

        $calendar = Calendar::query()->findOrFail($request->id_update);
        abort_unless($this->userCanEdit($calendar), 403);

        $calendar->update([
            'actividad' => $request->actividad_update,
            'descripcion' => $request->descripcion_update,
            'status' => $request->status_update,
            'start_date' => $request->date_post_update,
            'hora' => $request->time_update,
            'end_date' => $request->date_end_update,
        ]);

        return back();
    }

    public function get_calendar()
    {
        $calendars = $this->visibleCalendarsQuery()->get();
        $events = [];
        foreach ($calendars as $calendar) {
            $start = $calendar->start_date?->format('Y-m-d');
            if ($start === null || $start === '') {
                $legacyDate = $calendar->getAttribute('date');
                if ($legacyDate !== null && $legacyDate !== '') {
                    try {
                        $start = Carbon::parse($legacyDate)->format('Y-m-d');
                    } catch (\Throwable) {
                        $start = null;
                    }
                }
            }
            if ($start === null || $start === '') {
                continue;
            }
            $events[] = [
                'id' => $calendar->id,
                'title' => $calendar->actividad,
                'start' => $start,
                'end' => $calendar->end_date?->format('Y-m-d'),
                'backgroundColor' => $calendar->isTeam() ? '#6f42c1' : '#3788d8',
                'borderColor' => $calendar->isTeam() ? '#5a32a3' : '#2c6aa0',
            ];
        }

        return response()->json($events);
    }

    public function get_event(Request $request)
    {
        $request->validate(['id' => 'required']);

        $calendar = Calendar::query()->find($request->id);
        if (! $calendar || ! $this->userCanView($calendar)) {
            return response()->json(['message' => 'No encontrado'], 404);
        }

        $payload = $calendar->toArray();
        $payload['start_date'] = $calendar->start_date?->format('Y-m-d');
        $payload['end_date'] = $calendar->end_date?->format('Y-m-d');
        $payload['can_edit'] = $this->userCanEdit($calendar);

        return response()->json($payload);
    }

    private function visibleCalendarsQuery()
    {
        $userId = auth()->id();

        return Calendar::query()->where(function ($q) use ($userId) {
            $q->where(function ($p) use ($userId) {
                $p->where('user_id', $userId)
                    ->where(function ($s) {
                        $s->whereNull('scope')
                            ->orWhere('scope', Calendar::SCOPE_PERSONAL)
                            ->orWhere('scope', '');
                    });
            });
            if ($this->userCanReadTeam()) {
                $q->orWhere('scope', Calendar::SCOPE_TEAM);
            }
        });
    }

    private function userCanReadTeam(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        if ($this->userHasAdminRole()) {
            return true;
        }

        return $user->can('read team calendar') || $user->can('manage team calendar');
    }

    private function userCanManageTeam(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        if ($this->userHasAdminRole()) {
            return true;
        }

        return $user->can('manage team calendar');
    }

    /**
     * Respaldo: el rol «Admin» del seeder debe ver y gestionar calendario de equipo aunque
     * la matriz Spatie no tenga aún sincronizados los permisos nuevos (migración o caché).
     */
    private function userHasAdminRole(): bool
    {
        return (bool) auth()->user()?->hasRole('Admin');
    }

    private function userCanView(Calendar $calendar): bool
    {
        if ($calendar->isTeam()) {
            return $this->userCanReadTeam();
        }

        return (int) $calendar->user_id === (int) auth()->id();
    }

    private function userCanEdit(Calendar $calendar): bool
    {
        if ($calendar->isTeam()) {
            return $this->userCanManageTeam();
        }

        return (int) $calendar->user_id === (int) auth()->id();
    }
}
