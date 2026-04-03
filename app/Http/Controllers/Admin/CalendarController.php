<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Calendar;

class CalendarController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:read calendar']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $ids = Calendar::where('user_id', auth()->id())->pluck('id');
        return view('admin.calendar.index', compact('ids'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       
        $calendar = Calendar::create(['user_id' => auth()->user()->id, 'actividad' => $request->actividad, 'descripcion' => $request->descripcion, 'status' => $request->status, 'start_date' => $request->date_post, 'hora' => $request->time, 'end_date' => $request->date_end]);

        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {

        $calendar = Calendar::find($request->id_update);
        $calendar->update(['actividad' => $request->actividad_update, 'descripcion' => $request->descripcion_update, 'status' => $request->status_update, 'start_date' => $request->date_post_update, 'hora' => $request->time_update, 'end_date' => $request->date_end_update]);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function get_calendar(){
        
        $calendars = Calendar::where('user_id', auth()->id())->get();
        $data = array();
        foreach ($calendars as $calendar) {
            $data[$calendar->id]['id'] = $calendar->id; 

            $data[$calendar->id]['title'] = $calendar->actividad; 
            $data[$calendar->id]['start'] = $calendar->start_date; 
            $data[$calendar->id]['end'] = $calendar->end_date;  
            
        }

        return $data;
    }

    public function get_event(Request $request){
        $calendar = Calendar::find($request->id);

        return $calendar;
    }
}
