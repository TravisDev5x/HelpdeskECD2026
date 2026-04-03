<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Http\Requests\UpdateCompany;

class CompaniesController extends Controller
{
  public function __construct()
  {
    $this->middleware(['permission:create company'], ['only' => ['create', 'store']]);
    $this->middleware(['permission:read companies'], ['only' => ['index']]);
    $this->middleware(['permission:update company'], ['only' => ['edit', 'update']]);
    $this->middleware(['permission:delete company'], ['only' => ['destroy']]);
  }

  public function index()
  {
    $companies = Company::withTrashed()->get();
    return view('admin.companies.index', compact('companies'));
  }

  public function create()
  {
    return view('admin.companies.create');
  }

  public function store(Request $request)
  {
    $data = $request->validate([
      'name' => 'required|unique:companies|max:255|min:3',
    ]);

    $company = Company::create($data);

    return redirect()->route('admin.companies.index')->withFlash('Empresa guardada');
  }

  public function edit(Company $company)
  {
    return view('admin.companies.edit', compact('company'));
  }

  public function update(UpdateCompany $request, Company $company)
  {

    $company->update($request->validated());

    return back()->withFlash('Empresa actualizada');
  }

  public function destroy(Company $company)
  {
    $company->delete();
    return back()->withFlash('Empresa Suspendida');
  }

  public function restore($id)
  {
      $company = Company::withTrashed()->findOrFail($id);
      $company->restore();

      return back()->withFlash('Empresa Activada');
  }
}
