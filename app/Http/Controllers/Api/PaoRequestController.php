<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaoRequest;
use App\Http\Requests\UpdatePaoRequest;
use Illuminate\Support\Facades\DB;

class PaoRequestController extends Controller
{
    /** ---------------------------
     * Helper: Load request details
     * ---------------------------
     */
    private function loadRequestWithRelations($id)
    {
        $request = DB::table('pao_requests')->where('id', $id)->first();
        if (!$request) {
            return null;
        }

        // Get office code name
        $officeCode = DB::table('office_codes')->where('id', $request->office_code_id)->first();
        
        // Get office code budget (if it exists)
        $officeCodeBudget = $request->office_code_budget_id
            ? DB::table('office_code_budgets')->find($request->office_code_budget_id)
            : null;

        $groups = DB::table('pao_groups')
            ->where('request_id', $request->id)
            ->get()
            ->map(function ($group) {
                $group->group_object_expenditure = DB::table('group_object_expenditures')
                    ->find($group->group_id);
                return $group;
            });

        $objects = DB::table('pao_objects')
            ->where('request_id', $request->id)
            ->get()
            ->map(function ($object) {
                $object->object_expenditure = DB::table('object_expenditures')
                    ->find($object->object_expenditure_id);
                return $object;
            });

        return [
            'id'                    => $request->id,
            'office_code_id'        => $request->office_code_id,
            'office_code_budget_id' => $request->office_code_budget_id,
            'office_code_name'      => $officeCode->office_code ?? null,
            'office_code_budget'    => $officeCodeBudget,
            'created_by'            => $request->created_by,
            'updated_by'            => $request->updated_by,
            'deleted_by'            => $request->deleted_by,
            'created_at'            => $request->created_at,
            'updated_at'            => $request->updated_at,
            'groups'                => $groups,
            'objects'               => $objects,
            'total_amount'          => $objects->sum('amount'),
        ];
    }

    /** ---------------------------
     * GET /api/pao-requests
     * ---------------------------
     */
    public function index()
    {
        $requests = DB::table('pao_requests')
            ->join('office_codes', 'pao_requests.office_code_id', '=', 'office_codes.id')
            ->leftJoin('users', 'pao_requests.created_by', '=', 'users.id')
            ->leftJoin('office_code_budgets', 'pao_requests.office_code_budget_id', '=', 'office_code_budgets.id')
            ->leftJoin('annual_budgets', 'office_code_budgets.annual_budget_id', '=', 'annual_budgets.id') // Correct join for year
            ->select(
                'pao_requests.id as request_id',
                'pao_requests.created_by',
                'users.name as name',
                'pao_requests.created_at',
                'pao_requests.office_code_id',
                'office_codes.description as office_code_description',
                'pao_requests.office_code_budget_id',
                'annual_budgets.year as budget_year',
                'office_code_budgets.budget as budget' // ✅ Added budget field
            )
            ->orderBy('pao_requests.id', 'asc')
            ->get();

        if ($requests->isEmpty()) {
            return response()->json([]);
        }

        $requestIds = $requests->pluck('request_id');

        $allGroups = DB::table('pao_groups')
            ->join('group_object_expenditures', 'pao_groups.group_id', '=', 'group_object_expenditures.id')
            ->whereIn('pao_groups.request_id', $requestIds)
            ->select(
                'pao_groups.request_id',
                'pao_groups.id as pao_group_id',
                'group_object_expenditures.id as group_id',
                'group_object_expenditures.group_name'
            )
            ->get()
            ->groupBy('request_id');

        $allPaoGroupIds = $allGroups->flatten()->pluck('pao_group_id');

        $allObjects = DB::table('pao_objects')
            ->join('object_expenditures', 'pao_objects.object_expenditure_id', '=', 'object_expenditures.id')
            ->whereIn('pao_objects.group_id', $allPaoGroupIds)
            ->select(
                'pao_objects.group_id as pao_group_id',
                'object_expenditures.id as object_expenditure_id',
                'object_expenditures.object_expenditure as object_expenditure_name',
                'object_expenditures.account_code',
                'pao_objects.amount'
            )
            ->get()
            ->groupBy('pao_group_id');

        $data = $requests->map(function ($request) use ($allGroups, $allObjects) {
            $requestGroups = $allGroups->get($request->request_id, collect());
            
            $groupsWithObjects = $requestGroups->map(function ($group) use ($allObjects) {
                return [
                    'group_id'   => $group->group_id,
                    'group_name' => $group->group_name,
                    'objects'    => $allObjects->get($group->pao_group_id, collect()),
                ];
            });

            return [
                'request_id'              => $request->request_id,
                'created_by'              => $request->created_by,
                'name'                    => $request->name,
                'created_at'              => $request->created_at,
                'office_code_id'          => $request->office_code_id,
                'office_code_budget_id'   => $request->office_code_budget_id,
                'budget_year'             => $request->budget_year,
                'budget'                  => $request->budget, // ✅ Added budget field to response
                'office_code_description' => $request->office_code_description,
                'groups'                  => $groupsWithObjects,
            ];
        });

        return response()->json($data);
    }

    /** ---------------------------
     * GET /api/pao-requests/{id}
     * ---------------------------
     */
    public function show($id)
    {
        $data = $this->loadRequestWithRelations($id);
        return $data
            ? response()->json($data)
            : response()->json(['message' => 'Request not found'], 404);
    }

    /** ---------------------------
     * POST /api/pao-requests
     * ---------------------------
     */
    public function store(StorePaoRequest $request)
    {
        $validated = $request->validated();
        \Log::info('PAO Request store payload:', $validated);

        DB::beginTransaction();

        try {
            // Insert PAO request
            $requestId = DB::table('pao_requests')->insertGetId([
                'office_code_id' => $validated['office_code_id'],
                'office_code_budget_id' => $validated['office_code_budget_id'] ?? null,
                'created_by'     => $validated['created_by'],
                'updated_by'     => $validated['created_by'],
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // Insert groups and objects
            foreach ($validated['groups'] as $group) {
                if (!DB::table('group_object_expenditures')->where('id', $group['group_id'])->exists()) {
                    DB::rollBack();
                    return response()->json(['error' => "Group ID {$group['group_id']} does not exist"], 422);
                }

                $groupId = DB::table('pao_groups')->insertGetId([
                    'request_id' => $requestId,
                    'group_id'   => $group['group_id'],
                    'created_by' => $validated['created_by'],
                    'updated_by' => $validated['created_by'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($group['objects'] as $object) {
                    if (!DB::table('object_expenditures')->where('id', $object['object_expenditure_id'])->exists()) {
                        DB::rollBack();
                        return response()->json(['error' => "Object Expenditure ID {$object['object_expenditure_id']} does not exist"], 422);
                    }

                    DB::table('pao_objects')->insert([
                        'request_id'            => $requestId,
                        'group_id'              => $groupId,
                        'object_expenditure_id' => $object['object_expenditure_id'],
                        'amount'                => $object['amount'],
                        'created_by'            => $validated['created_by'],
                        'updated_by'            => $validated['created_by'],
                        'created_at'            => now(),
                        'updated_at'            => now(),
                    ]);
                }
            }

            // Fetch office code name for response
            $officeCode = DB::table('office_codes')->where('id', $validated['office_code_id'])->first();

            // Save to audit_logs with proper employee_id
            DB::table('audit_logs')->insert([
                'auditable_id'   => $requestId,
                'auditable_type' => 'App\Models\PaoRequest',
                'changes'        => json_encode([
                    'from' => null,
                    'to' => [
                        'office_code_id' => $validated['office_code_id'],
                        'office_code_budget_id' => $validated['office_code_budget_id'] ?? null,
                        'groups_count' => count($validated['groups'] ?? []),
                    ]
                ]),
                'remarks'        => 'Created new PAO request',
                'updated_by'     => auth()->user()->employee_id ?? null,
                'updated_at'     => now(),
            ]);

            DB::commit();

            return response()->json([
                'message'          => 'Request saved successfully',
                'request_id'       => $requestId,
                'office_code_name' => $officeCode->office_code ?? null,
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('PAO Request store failed', ['message' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /** ---------------------------
     * PUT/PATCH /api/pao-requests/{id}
     * ---------------------------
     */
    public function update(UpdatePaoRequest $request, $id)
    {
        $validated = $request->validated();
        // Use created_by from the payload for the user ID to maintain consistency with your store method
        $userId = $validated['created_by'] ?? auth()->id();

        DB::beginTransaction();

        try {
            $paoRequest = DB::table('pao_requests')->where('id', $id)->first();
            if (!$paoRequest) {
                return response()->json(['error' => "PAO Request {$id} not found"], 404);
            }

            // Capture original data before update
            $original = [
                'office_code_id' => $paoRequest->office_code_id,
                'office_code_budget_id' => $paoRequest->office_code_budget_id,
                'groups_count' => DB::table('pao_groups')->where('request_id', $id)->count(),
                'objects_count' => DB::table('pao_objects')->where('request_id', $id)->count(),
            ];

            // 1. Update the main PAO Request record
            $updateData = [
                'office_code_id'        => $validated['office_code_id'],
                'office_code_budget_id' => $validated['office_code_budget_id'] ?? null,
                'updated_by'            => $userId,
                'updated_at'            => now(),
            ];
            DB::table('pao_requests')->where('id', $id)->update($updateData);

            // --- Synchronization Logic ---

            // 2. First, delete all existing objects and groups for this request.
            // This ensures that any items removed on the frontend are also removed from the database.
            DB::table('pao_objects')->where('request_id', $id)->delete();
            DB::table('pao_groups')->where('request_id', $id)->delete();

            // 3. Now, re-insert the groups and objects from the validated payload.
            // This logic is similar to your 'store' method.
            if (!empty($validated['groups'])) {
                foreach ($validated['groups'] as $group) {
                    $paoGroupId = DB::table('pao_groups')->insertGetId([
                        'request_id' => $id, // Use the existing request ID
                        'group_id'   => $group['group_id'],
                        'created_by' => $userId,
                        'updated_by' => $userId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    if (!empty($group['objects'])) {
                        foreach ($group['objects'] as $object) {
                            DB::table('pao_objects')->insert([
                                'request_id'            => $id,
                                'group_id'              => $paoGroupId,
                                'object_expenditure_id' => $object['object_expenditure_id'],
                                'amount'                => $object['amount'],
                                'created_by'            => $userId,
                                'updated_by'            => $userId,
                                'created_at'            => now(),
                                'updated_at'            => now(),
                            ]);
                        }
                    }
                }
            }
            
            // --- End of Synchronization Logic ---

            // Save to audit_logs with proper structure
            DB::table('audit_logs')->insert([
                'auditable_id'   => $id,
                'auditable_type' => 'App\Models\PaoRequest',
                'changes'        => json_encode([
                    'from' => $original,
                    'to' => [
                        'office_code_id' => $validated['office_code_id'],
                        'office_code_budget_id' => $validated['office_code_budget_id'] ?? null,
                        'groups_count' => count($validated['groups'] ?? []),
                    ]
                ]),
                'remarks'        => 'Updated PAO request',
                'updated_by'     => auth()->user()->employee_id ?? null,
                'updated_at'     => now(),
            ]);

            DB::commit();

            return response()->json([
                'message'    => 'Request updated successfully',
                'request_id' => $id,
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('PAO Request update failed', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'An internal server error occurred during the update.'], 500);
        }
    }

    /** ---------------------------
     * DELETE /api/pao-requests/{id}
     * ---------------------------
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $paoRequest = DB::table('pao_requests')->where('id', $id)->first();
            
            if (!$paoRequest) {
                return response()->json(['error' => "PAO request ID {$id} not found"], 404);
            }

            // Capture original data before deletion
            $original = [
                'id' => $paoRequest->id,
                'office_code_id' => $paoRequest->office_code_id,
                'office_code_budget_id' => $paoRequest->office_code_budget_id,
            ];

            DB::table('pao_requests')->where('id', $id)->delete();

            // Audit log with proper structure
            DB::table('audit_logs')->insert([
                'auditable_id'   => $id,
                'auditable_type' => 'App\Models\PaoRequest',
                'changes'        => json_encode([
                    'from' => $original,
                    'to' => '[deleted]'
                ]),
                'remarks'        => 'Deleted PAO request',
                'updated_by'     => auth()->user()->employee_id ?? null,
                'updated_at'     => now(),
            ]);

            DB::commit();
            return response()->json(['message' => "PAO request {$id} deleted successfully"]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /** ---------------------------
     * DELETE /api/pao-requests/{requestId}/objects/{objectId}
     * ---------------------------
     */
    public function destroyObject($requestId, $objectExpenditureId)
    {
        DB::beginTransaction();

        try {
            $paoObject = DB::table('pao_objects')
                ->where('request_id', $requestId)
                ->where('object_expenditure_id', $objectExpenditureId)
                ->first();

            if (!$paoObject) {
                return response()->json([
                    'error' => "Object Expenditure ID {$objectExpenditureId} not found in Request ID {$requestId}"
                ], 404);
            }

            // Capture original data
            $original = [
                'id' => $paoObject->id,
                'request_id' => $paoObject->request_id,
                'object_expenditure_id' => $paoObject->object_expenditure_id,
                'amount' => $paoObject->amount,
            ];

            DB::table('pao_objects')->where('id', $paoObject->id)->delete();

            // Audit log with proper structure
            DB::table('audit_logs')->insert([
                'auditable_id'   => $paoObject->id,
                'auditable_type' => 'App\Models\PaoObject',
                'changes'        => json_encode([
                    'from' => $original,
                    'to' => '[deleted]'
                ]),
                'remarks'        => "Deleted object expenditure {$objectExpenditureId} from PAO request {$requestId}",
                'updated_by'     => auth()->user()->employee_id ?? null,
                'updated_at'     => now(),
            ]);

            DB::commit();
            return response()->json([
                'message' => "Object Expenditure ID {$objectExpenditureId} deleted from Request ID {$requestId}"
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /** ---------------------------
     * DELETE /api/pao-requests/{requestId}/groups/{groupId}
     * ---------------------------
     */
    public function destroyGroup($requestId, $groupId)
    {
        DB::beginTransaction();

        try {
            $paoGroupIds = DB::table('pao_groups')
                ->where('request_id', $requestId)
                ->where('group_id', $groupId)
                ->pluck('id');

            if ($paoGroupIds->isEmpty()) {
                return response()->json([
                    'error' => "No group with group_id {$groupId} found for request {$requestId}"
                ], 404);
            }

            $objectsDeleted = DB::table('pao_objects')
                ->where('request_id', $requestId)
                ->whereIn('group_id', $paoGroupIds)
                ->delete();

            $groupsDeleted = DB::table('pao_groups')
                ->whereIn('id', $paoGroupIds)
                ->delete();

            // Audit log with proper structure
            DB::table('audit_logs')->insert([
                'auditable_id'   => $requestId,
                'auditable_type' => 'App\Models\PaoRequest',
                'changes'        => json_encode([
                    'from' => [
                        'group_id' => $groupId,
                        'groups_count' => $groupsDeleted,
                        'objects_count' => $objectsDeleted,
                    ],
                    'to' => '[deleted]'
                ]),
                'remarks'        => "Deleted group {$groupId} from PAO request {$requestId}",
                'updated_by'     => auth()->user()->employee_id ?? null,
                'updated_at'     => now(),
            ]);

            DB::commit();
            return response()->json([
                'message'         => "Deleted group_id {$groupId} for request {$requestId}",
                'groups_deleted'  => $groupsDeleted,
                'objects_deleted' => $objectsDeleted,
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /** ---------------------------
     * GET /api/pao-requests/year/{year}
     * ---------------------------
     * Retrieves PAO requests filtered by a specific budget year.
     *
     * @param int $year The 4-digit budget year.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRequestsByYear($year)
    {
        // --- Step 1: Get all primary requests matching the year ---
        $requests = DB::table('pao_requests')
            ->join('office_codes', 'pao_requests.office_code_id', '=', 'office_codes.id')
            ->join('office_code_budgets', 'pao_requests.office_code_budget_id', '=', 'office_code_budgets.id')
            ->join('annual_budgets', 'office_code_budgets.annual_budget_id', '=', 'annual_budgets.id')
            ->where('annual_budgets.year', $year)
            ->select(
                'pao_requests.id',
                'office_codes.id as office_code_id',
                'office_codes.description',
                'office_code_budgets.budget',
                'annual_budgets.year'
            )
            ->get();

        if ($requests->isEmpty()) {
            return response()->json([]);
        }

        // --- Step 2: Efficiently fetch all nested objects for the found requests ---
        $requestIds = $requests->pluck('id');

        $allObjects = DB::table('pao_objects')
            ->join('object_expenditures', 'pao_objects.object_expenditure_id', '=', 'object_expenditures.id')
            ->join('group_object_expenditures', 'object_expenditures.group_id', '=', 'group_object_expenditures.id')
            ->whereIn('pao_objects.request_id', $requestIds)
            ->select(
                'pao_objects.request_id',
                'pao_objects.amount', // ✅ MODIFIED: Select the specific amount for each object
                'object_expenditures.id',
                'object_expenditures.object_expenditure',
                'object_expenditures.account_code',
                'object_expenditures.group_id',
                'group_object_expenditures.group_name'
            )
            ->get()
            ->groupBy('request_id');

        // --- Step 3: Combine the requests with their nested objects ---
        $data = $requests->map(function ($request) use ($allObjects) {
            $objectsForRequest = $allObjects->get($request->id, collect());

            return [
                'id' => $request->id,
                'office_code_id' => $request->office_code_id,
                'description' => $request->description,
                'budget' => $request->budget,
                'year' => $request->year,
                'objects' => $objectsForRequest->map(function ($object) {
                    return [
                        'id' => $object->id,
                        'object_expenditure' => $object->object_expenditure,
                        'account_code' => $object->account_code,
                        'group_id' => $object->group_id,
                        'group_name' => $object->group_name,
                        'amount' => $object->amount, // ✅ MODIFIED: Use the object's specific amount
                    ];
                }),
            ];
        });

        return response()->json($data);
    }
}