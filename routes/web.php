<?php

use App\Http\Controllers\ProfileController;
use App\Http\Middleware\CheckAdminMiddleWare;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\EmployeeController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(CheckAdminMiddleWare::class)->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::get('/companies', [CompanyController::class , 'index'])->name('companies');
Route::post('api/companies', [CompanyController::class, 'store']);
Route::delete('api/companies/{id}', [CompanyController::class, 'destroy']);
Route::post('api/companies/{id}', [CompanyController::class, 'update']);
Route::get('api/companies', [CompanyController::class, 'getCompanies']);

Route::get('/employees', [EmployeeController::class , 'index'])->name('employees');
Route::get('api/employees', [EmployeeController::class, 'getEmployees']);
Route::post('api/employees', [EmployeeController::class, 'store']);
Route::delete('api/employees/{id}', [EmployeeController::class, 'destroy']);
Route::post('api/employees/{id}', [EmployeeController::class, 'update']);


require __DIR__.'/auth.php';
