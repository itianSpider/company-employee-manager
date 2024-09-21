<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Employee;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewEmployeeAdded;
class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Company::all();     
        //
        return Inertia::render('Employees/index' , [
            'companies' => $data
        ]);
    }

    public function getEmployees(Request $request)
    {
        $pageSize = $request->input('pageSize', 5);  // Default to 5 if not provided
        $employees = Employee::paginate($pageSize);  // Use Laravel's pagination

        return response()->json([
            'data' => $employees->items(), // Only the paginated data
            'meta' => [
                'total' => $employees->total(),
                'current_page' => $employees->currentPage(),
                'per_page' => $employees->perPage(),
                'last_page' => $employees->lastPage(),  // Last page number for reference
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'company_id' => 'required|exists:companies,id',
        ]);
    
        $employee = new Employee();
        $employee->first_name = $request->first_name;
        $employee->last_name = $request->last_name;
        $employee->email = $request->email;
        $employee->phone = $request->phone;
        $employee->company_id = $request->company_id; 
    
        $employee->save();

        $company = Company::find($request->company_id);
        Mail::to($company->email)->send(new NewEmployeeAdded($employee));
    
        return response()->json([
            'message' => 'Employee created successfully.',
            'employee' => $employee,
        ], 201); 
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'company_id' => 'required|exists:companies,id',
        ]);

        $employee = Employee::findOrFail($id);
        $employee->first_name = $request->first_name;
        $employee->last_name = $request->last_name;
        $employee->email = $request->email;
        $employee->phone = $request->phone;
        $employee->company_id = $request->company_id;

        $employee->save();
        return response()->json(['message' => 'Employee updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        try {
            $employee = Employee::findOrFail($id);
            $employee->delete();

            return response()->json(['message' => 'Employee deleted successfully'], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting Employee', 'error' => $e->getMessage()], 500);
        }
    }
}
