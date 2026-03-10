<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreObrRequest;
use App\Http\Requests\UpdateObrRequest;
use App\Models\ObrRequestArchive;
use App\Models\ObrRequest;
use Illuminate\Http\JsonResponse;
use App\Models\PaperTrailStatus;
use App\Models\Step;
use App\Models\InternalStep;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class ObrRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ObrRequest::with([
            'obrObjects',
            'paoRequest.officeCode',
            'paoRequest.officeCodeBudget.annualBudget',
            'latestStatus.internalStep.step.officeCode',
            'rejections.rejectedByUser',
            'archives.archivedByUser',
            'latestArchive.archivedByUser'
        ]);

        // Filter by view mode (active or archived)
        $viewMode = $request->input('view_mode', 'active');
        
        if ($viewMode === 'archived') {
            $query->where('is_archived', true);
        } else {
            $query->where('is_archived', false);
        }

        $query->latest();

        // Filter by internal_step_id if provided
        if ($request->has('internal_step_id')) {
            $internalStepId = $request->input('internal_step_id');
            
            $query->whereHas('latestStatus', function($q) use ($internalStepId) {
                $q->where('internal_step_id', $internalStepId);
            });
        }

        $obrRequests = $query->paginate(10);

        $obrRequests->getCollection()->transform(function ($obr) {
            if ($obr->paoRequest && $obr->paoRequest->officeCodeBudget && $obr->paoRequest->officeCodeBudget->annualBudget) {
                $obr->year = $obr->paoRequest->officeCodeBudget->annualBudget->year;
            } else {
                $obr->year = null;
            }
            return $obr;
        });

        return response()->json($obrRequests);
    }

    /**
     * Get counts of OBRs grouped by internal step for a specific office and set
     * Only counts the LATEST status per request_id
     * EXCLUDES archived OBRs
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getCountsByInternalStep(Request $request): JsonResponse
    {
        $officeId = $request->input('office_id');
        $setId = $request->input('set_id');

        if (!$officeId || !$setId) {
            return response()->json([
                'message' => 'office_id and set_id are required'
            ], 400);
        }

        $counts = ObrRequest::from('obr_request')
            ->select('paper_trail_statuses.internal_step_id', \DB::raw('count(*) as count'))
            ->join('paper_trail_statuses', function ($join) {
                $join->on('obr_request.id', '=', 'paper_trail_statuses.request_id')
                    ->whereRaw('paper_trail_statuses.id = (
                        SELECT id FROM paper_trail_statuses
                        WHERE request_id = obr_request.id
                        ORDER BY created_at DESC
                        LIMIT 1
                    )');
            })
            ->join('internal_steps', 'paper_trail_statuses.internal_step_id', '=', 'internal_steps.id')
            ->join('steps', 'internal_steps.step_id', '=', 'steps.id')
            
            // CRITICAL FIX: Use = false instead of != true
            ->where('obr_request.is_archived', false)

            ->where('steps.set_id', $setId)
            ->where('steps.office_code_id', $officeId)
            ->whereNotNull('paper_trail_statuses.internal_step_id')
            ->groupBy('paper_trail_statuses.internal_step_id')
            ->pluck('count', 'internal_step_id');

        return response()->json($counts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreObrRequest $request)
    {
        $validated = $request->validated();

        $year = $validated['year'] ?? $request->input('year');

        if (!$year) {
            return response()->json(['message' => 'Year is required'], 422);
        }

        $obr = \DB::transaction(function () use ($validated, $year) {
            $obr = ObrRequest::create([
                'request_id'     => $validated['request_id'],
                'year'           => $year,
                'obr_no'         => $validated['obr_no'],
                'office_address' => $validated['office_address'],
                'is_archived'    => false, // Explicitly set on creation
            ]);

            foreach (($validated['obr_objects'] ?? []) as $item) {
                $obr->obrObjects()->create([
                    'object_expenditure_id' => $item['object_expenditure_id'],
                    'amount'                => $item['amount'],
                ]);
            }

            $setId = $validated['paper_trail_set_id'];
            $firstStep = Step::where('set_id', $setId)->orderBy('step_no')->firstOrFail();

            $firstInternal = InternalStep::where('step_id', $firstStep->id)
                ->orderBy('id')
                ->firstOrFail();

            PaperTrailStatus::create([
                'request_id' => $obr->id,
                'set_id'     => $setId,
                'step_id'    => $firstStep->id,
                'internal_step_id'     => $firstInternal->id,
            ]);

            // Audit log for OBR creation
            DB::table('audit_logs')->insert([
                'auditable_id'   => $obr->id,
                'auditable_type' => ObrRequest::class,
                'changes'        => json_encode([
                    'from' => null,
                    'to' => [
                        'obr_no' => $validated['obr_no'],
                        'request_id' => $validated['request_id'],
                        'year' => $year,
                        'office_address' => $validated['office_address'],
                        'initial_internal_step_id' => $firstInternal->id,
                    ],
                ]),
                'remarks'        => 'Created new OBR request',
                'updated_by'     => auth()->user()->employee_id ?? null,
                'updated_at'     => now(),
            ]);

            return $obr;
        });

        return response()->json(
            $obr->load(['obrObjects', 'latestStatus.internalStep']),
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(ObrRequest $obrRequest): JsonResponse
    {
        return response()->json($obrRequest->load('obrObjects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateObrRequest $request, ObrRequest $obrRequest): JsonResponse
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            // Capture original data before update
            $original = [
                'request_id' => $obrRequest->request_id,
                'obr_no' => $obrRequest->obr_no,
                'office_address' => $obrRequest->office_address,
                'obr_objects_count' => $obrRequest->obrObjects()->count(),
            ];

            $obrRequest->update([
                'request_id' => $validated['request_id'],
                'obr_no' => $validated['obr_no'],
                'office_address' => $validated['office_address'],
            ]);

            $obrRequest->obrObjects()->delete();

            if (!empty($validated['obr_objects'])) {
                $obrRequest->obrObjects()->createMany($validated['obr_objects']);
            }

            // Audit log for OBR update
            DB::table('audit_logs')->insert([
                'auditable_id'   => $obrRequest->id,
                'auditable_type' => ObrRequest::class,
                'changes'        => json_encode([
                    'from' => $original,
                    'to' => [
                        'request_id' => $validated['request_id'],
                        'obr_no' => $validated['obr_no'],
                        'office_address' => $validated['office_address'],
                        'obr_objects_count' => count($validated['obr_objects'] ?? []),
                    ],
                ]),
                'remarks'        => 'Updated OBR request info',
                'updated_by'     => auth()->user()->employee_id ?? null,
                'updated_at'     => now(),
            ]);

            DB::commit();

            return response()->json($obrRequest->load('obrObjects'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update OBR.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Archive an OBR request
     */
   public function archive(Request $request, $id)
    {
        $request->validate([
            'archive_reason' => 'required|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $obrRequest = ObrRequest::findOrFail($id);

            // Check if already archived
            if ($obrRequest->is_archived) {
                return response()->json([
                    'message' => 'OBR request is already archived'
                ], 400);
            }

            // Capture original state
            $original = [
                'is_archived' => $obrRequest->is_archived,
                'obr_no' => $obrRequest->obr_no,
            ];

            // Create archive record
            ObrRequestArchive::create([
                'obr_request_id' => $obrRequest->id,
                'archived_by' => Auth::id(),
                'archive_reason' => $request->archive_reason,
                'archived_at' => now(),
            ]);

            // Mark the OBR as archived
            $obrRequest->is_archived = true;
            $obrRequest->save();

            // Audit log for archiving
            DB::table('audit_logs')->insert([
                'auditable_id'   => $obrRequest->id,
                'auditable_type' => ObrRequest::class,
                'changes'        => json_encode([
                    'from' => $original,
                    'to' => [
                        'is_archived' => true,
                        'archive_reason' => $request->archive_reason,
                        'archived_by' => Auth::id(),
                    ],
                ]),
                'remarks'        => 'OBR request archived',
                'updated_by'     => auth()->user()->employee_id ?? null,
                'updated_at'     => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'OBR request archived successfully',
                'obr' => $obrRequest->fresh()->load(['latestArchive.archivedByUser']),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to archive OBR request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ObrRequest $obrRequest): JsonResponse
    {
        // Capture original data before deletion
        $original = [
            'id' => $obrRequest->id,
            'obr_no' => $obrRequest->obr_no,
            'request_id' => $obrRequest->request_id,
            'office_address' => $obrRequest->office_address,
            'is_archived' => $obrRequest->is_archived,
        ];

        // Audit log for deletion
        DB::table('audit_logs')->insert([
            'auditable_id'   => $obrRequest->id,
            'auditable_type' => ObrRequest::class,
            'changes'        => json_encode([
                'from' => $original,
                'to'   => '[deleted]',
            ]),
            'remarks'        => 'Deleted OBR request record',
            'updated_by'     => auth()->user()->employee_id ?? null,
            'updated_at'     => now(),
        ]);

        $obrRequest->delete();
        return response()->json(null, 204);
    }
}