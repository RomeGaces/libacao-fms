<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SalaryScheduleController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\Api\GroupObjectExpenditureController;
use App\Http\Controllers\Api\ObjectExpenditureController;
use App\Http\Controllers\Api\PaoRequestController;
use App\Http\Controllers\Api\OfficeCodeController;
use App\Http\Controllers\Api\AnnualBudgetController;
use App\Http\Controllers\Api\OfficeCodeBudgetController;
use App\Http\Controllers\Api\ObrRequestController;
use App\Http\Controllers\Api\PaperTrailSetController;
use App\Http\Controllers\Api\InternalStepController;
use App\Http\Controllers\Api\ObrProcessController;
use App\Http\Controllers\Api\ArchivedObrController;
use App\Http\Controllers\Api\EmployeeListController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\PlantillaController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| These routes are loaded by the RouteServiceProvider within a group
| which is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Employee routes
Route::apiResource('employees', EmployeeController::class);

// Department routes
Route::apiResource('departments', DepartmentController::class);

// Division routes
Route::apiResource('divisions', DivisionController::class);

// New: Get divisions by department
Route::get('departments/{department}/divisions', [DivisionController::class, 'getByDepartment']);

// Plantilla Routes
Route::apiResource('plantillas', PlantillaController::class);


// Salary schedule routes
Route::controller(SalaryScheduleController::class)
    ->prefix('salary-schedules')
    ->group(function () {
        Route::get('/', 'getSalarySchedule');               
        Route::put('/', 'updateSalarySchedules');            
        Route::post('/upload', 'import');                   
        Route::get('/template', 'exportTemplate');            
    });
Route::post('/createToken', [LoginController::class, 'createToken']);


// Authenticated user route
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json($request->user());
});

// Annual Budget
Route::middleware('auth:sanctum')->apiResource('annual-budgets', AnnualBudgetController::class);

// OBR Requests
Route::middleware('auth:sanctum')->get('/obr-requests/counts-by-internal-step', [ObrRequestController::class, 'getCountsByInternalStep']);
Route::middleware('auth:sanctum')->post('/obr-requests/{obrRequest}/return', [ObrProcessController::class, 'returnToPrevious']);
Route::middleware('auth:sanctum')->post('/obr-requests/{obrRequest}/process', [ObrProcessController::class, 'process']);
Route::middleware('auth:sanctum')->post('/obr-requests/{obrRequest}/reject', [ObrProcessController::class, 'reject']);
Route::middleware('auth:sanctum')->post('/obr-requests/{obrRequest}/archive', [ObrRequestController::class, 'archive']);
Route::middleware('auth:sanctum')->apiResource('obr-requests', ObrRequestController::class);

Route::middleware('auth:sanctum')->group(function () {
    // Route for getting budgets by a specific office code ID.
    Route::get('/office-code-budgets/by-office-code/{officeCodeId}', [OfficeCodeBudgetController::class, 'getByOfficeCode']);

    // Standard resource routes for Office Code Budgets.
    Route::apiResource('office-code-budgets', OfficeCodeBudgetController::class);
});

Route::middleware('auth:sanctum')->apiResource('paper-trail-sets', PaperTrailSetController::class);
Route::middleware('auth:sanctum')->get('/paper-trail-sets/{paperTrailSet}/used-steps', [PaperTrailSetController::class, 'getUsedSteps']);

// Office Codes
Route::middleware('auth:sanctum')->apiResource('office-codes', OfficeCodeController::class);
Route::middleware('auth:sanctum')->get('office-codes/find-by-description/{description}', [OfficeCodeController::class, 'getIdByDescription']);

// Group Object Expenditures
Route::middleware('auth:sanctum')->apiResource('group-object-expenditures', GroupObjectExpenditureController::class);

// Object Expenditures
Route::middleware('auth:sanctum')->apiResource('object-expenditures', ObjectExpenditureController::class);

// Audit Log Routes
// Audit Log Routes
Route::middleware('auth:sanctum')->prefix('audit-logs')->group(function () {
    Route::get('/', [AuditLogController::class, 'index']);
    Route::get('/types', [AuditLogController::class, 'getAuditableTypes']);
    Route::get('/users', [AuditLogController::class, 'getAuditUsers']);
    Route::get('/{id}', [AuditLogController::class, 'show']);
});

//PAO Request
Route::prefix('pao-requests')->middleware('auth:sanctum')->group(function () {
    Route::post('/', [PaoRequestController::class, 'store']);
    Route::get('/', [PaoRequestController::class, 'index']);
    Route::get('/year/{year}', [PaoRequestController::class, 'getRequestsByYear']);
    Route::get('/{id}', [PaoRequestController::class, 'show']);
    Route::match(['PUT', 'PATCH'], '/{id}', [PaoRequestController::class, 'update']);
    Route::delete('/{id}', [PaoRequestController::class, 'destroy']); // delete full request
    Route::delete('/{requestId}/objects/{objectId}', [PaoRequestController::class, 'destroyObject']); // delete single object
    Route::delete('/{requestId}/groups/{groupId}', [PaoRequestController::class, 'destroyGroup']);
});

// Route to get internal steps by the step's office code ID
Route::middleware('auth:sanctum')->get('/internal-steps/office/{officeCodeId}', [InternalStepController::class, 'getByOfficeCodeId'])
     ->where('officeCodeId', '[0-9]+'); // Ensures the parameter is numeric

// Get latests status
Route::middleware('auth:sanctum')->get('/obr-processes/latest-statuses', [ObrProcessController::class, 'getLatestStatusForEachRequest']);

// Get latests status
Route::middleware('auth:sanctum')->get('/obr-processes/latest-statuses/{officeCodeId}', [ObrProcessController::class, 'getLatestStatusForEachRequest']);

// Get internal steps by both office code ID and set ID
Route::middleware('auth:sanctum')->get('/internal-steps/office/{officeCodeId}/{setID}', [InternalStepController::class, 'getByOfficeAndSet'])
     ->where([
         'officeCodeId' => '[0-9]+',
         'setID' => '[0-9]+'
     ]);

// --- Set Route (using InternalStepController) ---
Route::get('/sets', [InternalStepController::class, 'getAllSets']);

// Archived Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/archived-obrs', [ArchivedObrController::class, 'index']);
    Route::get('/archived-obrs/filters/archivers', [ArchivedObrController::class, 'getArchivers']);
    Route::get('/archived-obrs/{id}', [ArchivedObrController::class, 'show']);
    Route::delete('/archived-obrs/{id}', [ArchivedObrController::class, 'destroy']);
});

// Department Employees Routes - REORGANIZED WITH SPECIFIC ROUTES FIRST
Route::middleware('auth:sanctum')->prefix('department-employees')->group(function () {
    // Specific routes MUST come before dynamic routes
    Route::get('accessible-steps', [EmployeeListController::class, 'getEmployeeAccessibleSteps']);
    Route::get('debug', [EmployeeListController::class, 'debug']);
    Route::get('filters/civil-status', [EmployeeListController::class, 'getCivilStatusOptions']);
    Route::post('step-access', [EmployeeListController::class, 'updateStepAccess']);
    
    // Dynamic routes come after - with numeric constraint
    Route::get('{id}', [EmployeeListController::class, 'show'])->where('id', '[0-9]+');
    
    // Index route last
    Route::get('/', [EmployeeListController::class, 'index']);
});