<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\ObrRequest;
use App\Models\ObrRejection;
use App\Models\ObrRequestArchive;
use App\Models\PaperTrailStatus;
use App\Models\InternalStep;
use App\Models\Step;

class ObrProcessController extends Controller
{
    /**
     * Process an OBR and start its paper trail.
     * POST /obr-requests/{obrRequest}/process
     *
     * @param ObrRequest $obrRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function process(ObrRequest $obrRequest): JsonResponse
    {
        try {
            // Get the current latest status
            $currentStatus = PaperTrailStatus::where('request_id', $obrRequest->id)
                ->orderBy('id', 'desc')
                ->firstOrFail();

            // Find the next internal step in the sequence
            $currentInternalStep = InternalStep::findOrFail($currentStatus->internal_step_id);
            $currentStep = Step::findOrFail($currentInternalStep->step_id);
            
            // Get all internal steps for this step, ordered
            $internalSteps = InternalStep::where('step_id', $currentStep->id)
                ->orderBy('id')
                ->get();
            
            $currentIndex = $internalSteps->search(function($item) use ($currentInternalStep) {
                return $item->id === $currentInternalStep->id;
            });

            $nextInternalStep = null;

            // Check if there's a next internal step in the same step
            if ($currentIndex !== false && $currentIndex < $internalSteps->count() - 1) {
                $nextInternalStep = $internalSteps[$currentIndex + 1];
            } else {
                // Need to go to next step's first internal step
                $nextStep = Step::where('set_id', $currentStep->set_id)
                    ->where('step_no', '>', $currentStep->step_no)
                    ->orderBy('step_no')
                    ->first();

                if ($nextStep) {
                    $nextInternalStep = InternalStep::where('step_id', $nextStep->id)
                        ->orderBy('id')
                        ->first();
                }
            }

            if (!$nextInternalStep) {
                return response()->json([
                    'message' => 'Cannot proceed: this is the final step in the process.'
                ], 400);
            }

            // Create new status record for the next step
            $newStatus = PaperTrailStatus::create([
                'request_id' => $obrRequest->id,
                'set_id' => $currentStatus->set_id,
                'step_id' => $nextInternalStep->step_id,
                'internal_step_id' => $nextInternalStep->id,
            ]);

            // Audit log for processing to next step
            DB::table('audit_logs')->insert([
                'auditable_id'   => $obrRequest->id,
                'auditable_type' => ObrRequest::class,
                'changes'        => json_encode([
                    'from' => [
                        'internal_step_id' => $currentStatus->internal_step_id,
                        'internal_step_name' => $currentInternalStep->internal_step_name ?? null,
                    ],
                    'to' => [
                        'internal_step_id' => $nextInternalStep->id,
                        'internal_step_name' => $nextInternalStep->internal_step_name ?? null,
                    ],
                ]),
                'remarks'        => 'OBR processed to next step',
                'updated_by'     => auth()->user()->employee_id ?? null,
                'updated_at'     => now(),
            ]);

            return response()->json([
                'message' => 'OBR successfully processed to next step.',
                'obr' => $obrRequest->load(['latestStatus.internalStep'])
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process OBR', [
                'obr_id' => $obrRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Failed to process OBR.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the latest paper trail status for each request_id, including its associated step.
     * GET /obr-processes/latest-statuses
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLatestStatusForEachRequest()
    {
        try {
            // This subquery finds the highest 'id' (most recent entry) for each 'request_id' group.
            $latestStatusIds = PaperTrailStatus::select(DB::raw('MAX(id) as last_id'))
                ->groupBy('request_id')
                ->pluck('last_id');

            // --- MODIFICATION IS HERE ---
            // This fetches the full records for only those latest IDs.
            // We use with('step') to eager load the related step data for each status.
            $latestStatuses = PaperTrailStatus::whereIn('id', $latestStatusIds)
                ->with('step') // Eager load the 'step' relationship
                ->get();

            return response()->json($latestStatuses);

        } catch (\Exception $e) {
            Log::error("Failed to get latest statuses: " . $e->getMessage());
            return response()->json(['message' => 'An error occurred while fetching latest statuses.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Return OBR to previous process step
     * POST /obr-requests/{obrRequest}/return
     */
    public function returnToPrevious(ObrRequest $obrRequest): JsonResponse
    {
        try {
            // Get the current latest status
            $currentStatus = PaperTrailStatus::where('request_id', $obrRequest->id)
                ->orderBy('id', 'desc')
                ->firstOrFail();

            // Find the previous internal step in the sequence
            $currentInternalStep = InternalStep::findOrFail($currentStatus->internal_step_id);
            $currentStep = Step::findOrFail($currentInternalStep->step_id);
            
            // Get all internal steps for this step, ordered
            $internalSteps = InternalStep::where('step_id', $currentStep->id)
                ->orderBy('id')
                ->get();
            
            $currentIndex = $internalSteps->search(function($item) use ($currentInternalStep) {
                return $item->id === $currentInternalStep->id;
            });

            $previousInternalStep = null;

            // Check if there's a previous internal step in the same step
            if ($currentIndex > 0) {
                $previousInternalStep = $internalSteps[$currentIndex - 1];
            } else {
                // Need to go to previous step's last internal step
                $previousStep = Step::where('set_id', $currentStep->set_id)
                    ->where('step_no', '<', $currentStep->step_no)
                    ->orderBy('step_no', 'desc')
                    ->first();

                if ($previousStep) {
                    $previousInternalStep = InternalStep::where('step_id', $previousStep->id)
                        ->orderBy('id', 'desc')
                        ->first();
                }
            }

            if (!$previousInternalStep) {
                return response()->json([
                    'message' => 'Cannot return: this is the first step in the process.'
                ], 400);
            }

            // Create new status record for the previous step
            $newStatus = PaperTrailStatus::create([
                'request_id' => $obrRequest->id,
                'set_id' => $currentStatus->set_id,
                'step_id' => $previousInternalStep->step_id,
                'internal_step_id' => $previousInternalStep->id,
            ]);

            // Audit log for returning to previous step
            DB::table('audit_logs')->insert([
                'auditable_id'   => $obrRequest->id,
                'auditable_type' => ObrRequest::class,
                'changes'        => json_encode([
                    'from' => [
                        'internal_step_id' => $currentStatus->internal_step_id,
                        'internal_step_name' => $currentInternalStep->internal_step_name ?? null,
                    ],
                    'to' => [
                        'internal_step_id' => $previousInternalStep->id,
                        'internal_step_name' => $previousInternalStep->internal_step_name ?? null,
                    ],
                ]),
                'remarks'        => 'OBR returned to previous process step',
                'updated_by'     => auth()->user()->employee_id ?? null,
                'updated_at'     => now(),
            ]);

            return response()->json([
                'message' => 'OBR successfully returned to previous process.',
                'obr' => $obrRequest->load(['latestStatus.internalStep'])
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to return OBR', [
                'obr_id' => $obrRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Failed to return OBR to previous process.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject OBR and return to initial step of previous office
     * POST /obr-requests/{obrRequest}/reject
     *
     * @param Request $request
     * @param ObrRequest $obrRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function reject(Request $request, ObrRequest $obrRequest): JsonResponse
    {
        $request->validate([
            'rejection_details' => 'required|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Get current status
            $currentStatus = PaperTrailStatus::where('request_id', $obrRequest->id)
                ->orderBy('id', 'desc')
                ->firstOrFail();
            
            if (!$currentStatus) {
                return response()->json([
                    'message' => 'No current status found for this OBR.'
                ], 400);
            }

            $currentInternalStep = InternalStep::findOrFail($currentStatus->internal_step_id);
            $currentStep = Step::findOrFail($currentInternalStep->step_id);

            // Get all steps in the current set, ordered by step_no
            $allStepsInSet = Step::where('set_id', $currentStep->set_id)
                ->orderBy('step_no')
                ->get();

            // Find the previous step (previous office)
            $previousStep = null;
            foreach ($allStepsInSet as $step) {
                if ($step->id == $currentStep->id) {
                    break;
                }
                $previousStep = $step;
            }

            if (!$previousStep) {
                return response()->json([
                    'message' => 'Cannot reject from the first office in the workflow.'
                ], 400);
            }

            // Find the initial (first) internal step of the previous office/step
            $targetInternalStep = InternalStep::where('step_id', $previousStep->id)
                ->orderBy('id', 'asc')
                ->first();

            if (!$targetInternalStep) {
                return response()->json([
                    'message' => 'Could not find initial step for previous office.'
                ], 400);
            }

            // Create new status record pointing to the initial step of previous office
            $newStatus = PaperTrailStatus::create([
                'request_id' => $obrRequest->id,
                'set_id' => $currentStatus->set_id,
                'step_id' => $previousStep->id,
                'internal_step_id' => $targetInternalStep->id,
            ]);

            // Record the rejection
            $rejection = ObrRejection::create([
                'request_id' => $obrRequest->id,
                'rejection_details' => $request->rejection_details,
                'rejected_by' => Auth::id(),
            ]);

            // Audit log for rejection
            DB::table('audit_logs')->insert([
                'auditable_id'   => $obrRequest->id,
                'auditable_type' => ObrRequest::class,
                'changes'        => json_encode([
                    'from' => [
                        'internal_step_id' => $currentStatus->internal_step_id,
                        'internal_step_name' => $currentInternalStep->internal_step_name ?? null,
                    ],
                    'to' => [
                        'internal_step_id' => $targetInternalStep->id,
                        'internal_step_name' => $targetInternalStep->internal_step_name ?? null,
                        'rejection_details' => $request->rejection_details,
                    ],
                ]),
                'remarks'        => 'OBR rejected and returned to initial step of previous office',
                'updated_by'     => auth()->user()->employee_id ?? null,
                'updated_at'     => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'OBR rejected successfully and returned to previous office.',
                'data' => $obrRequest->fresh()->load('latestStatus.internalStep')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to reject OBR: " . $e->getMessage());
            return response()->json([
                'message' => 'Failed to reject OBR: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Archive OBR request
     * POST /obr-requests/{obrRequest}/archive
     *
     * @param Request $request
     * @param ObrRequest $obrRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function archive(Request $request, ObrRequest $obrRequest): JsonResponse
    {
        $request->validate([
            'archive_reason' => 'required|string|min:10|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Log for debugging
            Log::info('Archiving OBR', [
                'obr_id' => $obrRequest->id,
                'user_id' => Auth::id(),
                'reason' => $request->archive_reason
            ]);

            // Capture original state
            $original = [
                'is_archived' => $obrRequest->is_archived,
                'obr_no' => $obrRequest->obr_no,
            ];

            // Create archive record
            $archive = ObrRequestArchive::create([
                'obr_request_id' => $obrRequest->id,
                'archived_by' => Auth::id(),
                'archive_reason' => $request->archive_reason,
                'archived_at' => now(),
            ]);

            // Update the OBR to mark as archived
            $obrRequest->is_archived = true;
            $obrRequest->save();

            // Verify it was updated
            $obrRequest->refresh();
            
            Log::info('OBR archived successfully', [
                'obr_id' => $obrRequest->id,
                'is_archived' => $obrRequest->is_archived,
                'archive_id' => $archive->id
            ]);

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
                'obr_request' => $obrRequest,
                'archive' => $archive->load('archivedByUser'),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to archive OBR', [
                'obr_id' => $obrRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to archive OBR request',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}