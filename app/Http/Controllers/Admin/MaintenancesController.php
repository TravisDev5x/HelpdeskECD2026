<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Product;
use App\Models\Maintenance;

class MaintenancesController extends Controller
{
  public function __construct()
  {
    $this->middleware(['permission:read products'], ['only' => ['index', 'show', 'get_mantenances']]);
    $this->middleware(['permission:update product'], ['only' => ['edit', 'update', 'destroy']]);
  }

  public function index()
  {
    $maintenances = Maintenance::get();
    return view('admin.maintenances.index', compact('maintenances'));
  }

  public function create()
  {
    //
  }

  public function show(Request $request, $id)
  {
    $product = Product::whereId($id)->first();
    $maintenances = Maintenance::with('user')->where('product_id', $id)
      ->orderBy('maintenance_date', 'desc')->get();
    return view('admin.maintenances.show', compact('product', 'maintenances'));
  }

  public function edit(Request $request, $id)
  {
    $product = Product::whereId($id)->first();
    $maintenances = Maintenance::with('user')->where('product_id', $id)
      ->orderBy('maintenance_date', 'desc')->get();
    return view('admin.maintenances.create', compact('product', 'maintenances'));
  }

  public function update(Request $request, $id)
  {
      $data = $request->validate([
          'maintenance' => 'required|string',
          'maintenance_date' => 'required|date',
          'pdf_mantenimiento' => 'nullable|mimes:pdf',
      ]);
  
      $data['user_id'] = auth()->id();
      $data['product_id'] = $id;
  
      $product = Product::findOrFail($id);
  
      $product->last_maintenance_date = $product->maintenance_date;
      $product->maintenance = $request->maintenance;
      $product->maintenance_date = $request->maintenance_date;
      $product->save();
  
      if ($request->hasFile('pdf_mantenimiento')) {
          $file = $request->file('pdf_mantenimiento');

          $fileName = uniqid() . '_' . $file->getClientOriginalName();

          $file->storeAs('helpdesk/mantenimientos', $fileName, 'storage_celer2');
          
          $data['pdf_mantenimiento'] = $fileName;
      }
  
      $maintenance = Maintenance::create($data);
  
      return back()->with('flash', 'Mantenimiento guardado');
  }

  public function destroy($id)
  {
    //
  }

  public function get_mantenances()
  {
    $maintenances = Maintenance::getTableMaintenances();
    return Datatables::of($maintenances)
      ->make(true);
  }
}
