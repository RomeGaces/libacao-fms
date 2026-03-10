<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaperTrailSetRequest;
use App\Models\Set;
use App\Models\PaperTrailStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PaperTrailSetController extends Controller
{
    public function index(): JsonResponse
    {
        $sets = Set::with('steps.internalSteps', 'steps.officeCode')->latest()->paginate(10);
        
        // Add is_in_use flag to each set
        $sets->getCollection()->transform(function ($set) {
            $set->is_in_use = PaperTrailStatus::where('set_id', $set->id)->exists();
            return $set;
        });
        
        return response()->json($sets);
    }

    public function store(StorePaperTrailSetRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $set = DB::transaction(function () use ($validated) {
            $latestSetNo = Set::max('set_no');
            $nextSetNo = ($latestSetNo ?? 0) + 1;

            $set = Set::create([
                'set_no' => $nextSetNo,
                'office_code' => $validated['office_code'],
            ]);

            $stepsCount = 0;
            $internalStepsCount = 0;

            foreach ($validated['steps'] as $stepIndex => $stepData) {
                $step = $set->steps()->create([
                    'office_code_id' => $stepData['office_code_id'], 
                    'step_no' => $stepIndex + 1,
                ]);
                $stepsCount++;

                if (!empty($stepData['internal_steps'])) {
                    foreach ($stepData['internal_steps'] as $internalStepData) {
                        $step->internalSteps()->create([
                            'approval_title' => $internalStepData['approval_title'],
                        ]);
                        $internalStepsCount++;
                    }
                }
            }

            // Audit log for paper trail set creation
            DB::table('audit_logs')->insert([
                'auditable_id'   => $set->id,
                'auditable_type' => Set::class,
                'changes'        => json_encode([
                    'from' => null,
                    'to' => [
                        'set_no' => $nextSetNo,
                        'office_code' => $validated['office_code'],
                        'steps_count' => $stepsCount,
                        'internal_steps_count' => $internalStepsCount,
                    ],
                ]),
                'remarks'        => 'Created new paper trail set',
                'updated_by'     => auth()->user()->employee_id ?? null,
                'updated_at'     => now(),
            ]);

            return $set;
        });

        $set->load('steps.internalSteps', 'steps.officeCode');
        $set->is_in_use = false; // New sets are never in use
        
        return response()->json($set, 201);
    }

    public function show(Set $paperTrailSet): JsonResponse
    {
        $paperTrailSet->load('steps.internalSteps', 'steps.officeCode');
        $paperTrailSet->is_in_use = PaperTrailStatus::where('set_id', $paperTrailSet->id)->exists();
        
        return response()->json($paperTrailSet);
    }

    public function update(StorePaperTrailSetRequest $request, Set $paperTrailSet): JsonResponse
    {
        $validated = $request->validated();

        // Capture original data before update
        $original = [
            'set_no' => $paperTrailSet->set_no,
            'office_code' => $paperTrailSet->office_code,
            'steps_count' => $paperTrailSet->steps()->count(),
            'internal_steps_count' => $paperTrailSet->steps()->with('internalSteps')->get()->sum(function($step) {
                return $step->internalSteps->count();
            }),
        ];

        $updatedSet = DB::transaction(function () use ($validated, $paperTrailSet, $original) {
            $paperTrailSet->update([
                'office_code' => $validated['office_code'],
            ]);

            $incomingStepIds = [];
            $totalInternalSteps = 0;

            foreach ($validated['steps'] as $stepIndex => $stepData) {
                $step = null;
                
                // Check if this step already exists
                if (!empty($stepData['id'])) {
                    // Find existing step - IMPORTANT: Don't scope by parent, use global ID
                    $step = $paperTrailSet->steps()->where('id', $stepData['id'])->first();
                    
                    if ($step) {
                        // Update existing step
                        $step->update([
                            'office_code_id' => $stepData['office_code_id'],
                            'step_no' => $stepIndex + 1,
                        ]);
                    }
                }
                
                // If step doesn't exist (no ID or not found), create new one
                if (!$step) {
                    $step = $paperTrailSet->steps()->create([
                        'office_code_id' => $stepData['office_code_id'],
                        'step_no' => $stepIndex + 1,
                    ]);
                }

                $incomingStepIds[] = $step->id;

                // Handle internal steps
                $incomingInternalStepIds = [];
                
                if (!empty($stepData['internal_steps'])) {
                    foreach ($stepData['internal_steps'] as $internalStepData) {
                        $internalStep = null;
                        
                        // Check if internal step exists
                        if (!empty($internalStepData['id'])) {
                            $internalStep = $step->internalSteps()->where('id', $internalStepData['id'])->first();
                            
                            if ($internalStep) {
                                // Update existing internal step
                                $internalStep->update([
                                    'approval_title' => $internalStepData['approval_title'],
                                ]);
                            }
                        }
                        
                        // If internal step doesn't exist, create it
                        if (!$internalStep) {
                            $internalStep = $step->internalSteps()->create([
                                'approval_title' => $internalStepData['approval_title'],
                            ]);
                        }
                        
                        $incomingInternalStepIds[] = $internalStep->id;
                        $totalInternalSteps++;
                    }
                }
                
                // Delete internal steps that weren't in the request
                $step->internalSteps()->whereNotIn('id', $incomingInternalStepIds)->delete();
            }
            
            // Delete steps that weren't in the request
            $paperTrailSet->steps()->whereNotIn('id', $incomingStepIds)->delete();

            // Audit log for paper trail set update
            DB::table('audit_logs')->insert([
                'auditable_id'   => $paperTrailSet->id,
                'auditable_type' => Set::class,
                'changes'        => json_encode([
                    'from' => $original,
                    'to' => [
                        'set_no' => $paperTrailSet->set_no,
                        'office_code' => $validated['office_code'],
                        'steps_count' => count($incomingStepIds),
                        'internal_steps_count' => $totalInternalSteps,
                    ],
                ]),
                'remarks'        => 'Updated paper trail set info',
                'updated_by'     => auth()->user()->employee_id ?? null,
                'updated_at'     => now(),
            ]);
            
            return $paperTrailSet;
        });

        $updatedSet->load('steps.internalSteps', 'steps.officeCode');
        $updatedSet->is_in_use = PaperTrailStatus::where('set_id', $updatedSet->id)->exists();
        
        return response()->json($updatedSet);
    }

    public function destroy(Set $paperTrailSet): JsonResponse
    {
        // Check if the set is in use
        $isInUse = PaperTrailStatus::where('set_id', $paperTrailSet->id)->exists();
        
        if ($isInUse) {
            return response()->json([
                'message' => 'Cannot delete this paper trail set because it is currently in use by one or more OBR requests.'
            ], 422);
        }

        // Capture original data before deletion
        $original = [
            'set_no' => $paperTrailSet->set_no,
            'office_code' => $paperTrailSet->office_code,
            'steps_count' => $paperTrailSet->steps()->count(),
        ];

        // Audit log for deletion
        DB::table('audit_logs')->insert([
            'auditable_id'   => $paperTrailSet->id,
            'auditable_type' => Set::class,
            'changes'        => json_encode([
                'from' => $original,
                'to'   => '[deleted]',
            ]),
            'remarks'        => 'Deleted paper trail set record',
            'updated_by'     => auth()->user()->employee_id ?? null,
            'updated_at'     => now(),
        ]);
        
        $paperTrailSet->delete();
        return response()->json(null, 204);
    }

    /**
     * Get all step IDs and internal step IDs that are currently in use
     */
    public function getUsedSteps(Set $paperTrailSet): JsonResponse
    {
        $usedStepIds = PaperTrailStatus::where('set_id', $paperTrailSet->id)
            ->distinct()
            ->pluck('step_id')
            ->toArray();
        
        $usedInternalStepIds = PaperTrailStatus::where('set_id', $paperTrailSet->id)
            ->distinct()
            ->pluck('internal_step_id')
            ->toArray();

        return response()->json([
            'step_ids' => $usedStepIds,
            'internal_step_ids' => $usedInternalStepIds
        ]);
    }
}