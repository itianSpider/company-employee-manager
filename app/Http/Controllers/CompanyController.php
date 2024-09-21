<?php

namespace App\Http\Controllers;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Company;
class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $data = Company::all();     
        //
        return Inertia::render('Companies/index' , [
            'companies' => $data
        ]);
        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }


    public function getCompanies(Request $request)
    {
        $pageSize = $request->input('pageSize', 5);  // Default to 5 if not provided
        $companies = Company::paginate($pageSize);  // Use Laravel's pagination

        return response()->json([
            'data' => $companies->items(), // Only the paginated data
            'meta' => [
                'total' => $companies->total(),
                'current_page' => $companies->currentPage(),
                'per_page' => $companies->perPage(),
                'last_page' => $companies->lastPage(),  // Last page number for reference
            ],
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);
        $company = new Company();
        $company->name = $request->name;
        $company->email = $request->email;
        $company->website = $request->website;

        if ($request->hasFile('logo')) {
            $filename = $request->file('logo')->store('logos', 'public');
            $company->logo = $filename; 
        }

        $company->save();
        return response()->json(['message' => 'Company created successfully!'], 200);


    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
      
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $company = Company::findOrFail($id);
        $company->name = $request->name;
        $company->email = $request->email;
        $company->website = $request->website;

        if ($request->hasFile('logo')) {
            $filename = $request->file('logo')->store('logos', 'public');
            $company->logo = $filename; 
        }
        $company->save();
        return response()->json(['message' => 'Company updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        try {
            $company = Company::findOrFail($id);
            $company->delete();

            return response()->json(['message' => 'Company deleted successfully'], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting company', 'error' => $e->getMessage()], 500);
        }
    }
}
