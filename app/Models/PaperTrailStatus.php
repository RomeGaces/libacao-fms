<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// We need these models to find the next step
use App\Models\Set;
use App\Models\Step; // <-- ✅ THIS IS THE FIX
use App\Models\InternalStep;

class PaperTrailStatus extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'paper_trail_statuses';

    protected $fillable = [
        'request_id',
        'set_id',
        'step_id',
        'internal_step_id',
        // 'approval_title',   // <-- ❌ REMOVED.
        'created_by',
        'updated_by',
    ];

    public function request(): BelongsTo
    {
        // Renamed from request() to obrRequest() to match ObrRequest model
        // and avoid conflicts with Laravel's internal request() helper
        return $this->belongsTo(ObrRequest::class, 'request_id');
    }

    public function set(): BelongsTo
    {
        return $this->belongsTo(Set::class);
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(Step::class);
    }

    /**
     * Relationship to the InternalStep model
     */
    public function internalStep(): BelongsTo
    {
        return $this->belongsTo(InternalStep::class, 'internal_step_id');
    }

    /**
     * Finds the next internal step in the paper trail.
     *
     * @param int $obrRequestId The ID of the ObrRequest.
     * @return InternalStep|null The next InternalStep, or null if the trail is complete.
     * @throws \Exception If the current state is invalid.
     */
    public static function getNextInternalStep(int $obrRequestId): ?InternalStep
    {
        // 1. Find the most recent status for this request
        $latestStatus = self::where('request_id', $obrRequestId)
            ->orderBy('created_at', 'desc')
            ->first();

        // 2. If there is no status, the trail hasn't started.
        if (!$latestStatus) {
            throw new \Exception("Cannot find next step: No paper trail has been started for OBR ID $obrRequestId.");
        }

        // 3. Find the *current* InternalStep from the latest status
        $currentInternalStep = InternalStep::with('step.set')
            ->find($latestStatus->internal_step_id);
            
        if (!$currentInternalStep) {
            throw new \Exception("Could not find current internal step with ID $latestStatus->internal_step_id.");
        }

        // 4. Try to find the *next* InternalStep *within the same Step*
        $nextInternalStep = InternalStep::where('step_id', $currentInternalStep->step_id)
            ->where('id', '>', $currentInternalStep->id) // or ->where('order', '>', $currentInternalStep->order)
            ->orderBy('id', 'asc')
            ->first();

        // If we found one, this is our next step
        if ($nextInternalStep) {
            return $nextInternalStep;
        }

        // 5. If not, we are at the end of a Step. Find the *next* Step in the Set.
        $currentStep = $currentInternalStep->step;
        
        if (!$currentStep) {
             throw new \Exception("Could not find the parent Step for InternalStep ID $currentInternalStep->id.");
        }

        $set = $currentStep->set;

        if (!$set) {
            throw new \Exception("Could not find the Set associated with Step ID $currentStep->id.");
        }
        
        $nextStep = Step::where('set_id', $set->id)
            ->where('order', '>', $currentStep->order) // Assumes 'order' column on 'steps' table
            ->orderBy('order', 'asc')
            ->first();

        // 6. If there is no next Step, the paper trail is complete.
        if (!$nextStep) {
            return null; // Trail is finished
        }

        // 7. If there IS a next Step, find the *first* InternalStep within it.
        $firstInternalOfNextStep = $nextStep->internalSteps()->orderBy('id', 'asc')->first();
        
        if (!$firstInternalOfNextStep) {
            throw new \Exception("The next Step (ID: $nextStep->id) has no Internal Steps defined.");
        }

        return $firstInternalOfNextStep;
    }
}