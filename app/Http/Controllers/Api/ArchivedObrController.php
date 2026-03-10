<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ObrRequest;
use App\Models\ObrRequestArchive;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ArchivedObrController extends Controller
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
            'latestStatus.internalStep',
            'rejections.rejectedByUser',
            'archives.archivedByUser',
            'latestArchive.archivedByUser'
        ])
        ->where('is_archived', true)
        ->latest();

        // Filter by search (OBR Number or Office Address)
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('obr_no', 'like', "%{$search}%")
                  ->orWhere('office_address', 'like', "%{$search}%");
            });
        }

        // Filter by archived_by
        if ($request->has('archived_by') && $request->archived_by) {
            $archivedBy = $request->input('archived_by');
            
            $query->whereHas('latestArchive', function($q) use ($archivedBy) {
                $q->where('archived_by', $archivedBy);
            });
        }

        // Filter by date_from
        if ($request->has('date_from') && $request->date_from) {
            $dateFrom = $request->input('date_from');
            
            $query->whereHas('latestArchive', function($q) use ($dateFrom) {
                $q->whereDate('archived_at', '>=', $dateFrom);
            });
        }

        // Filter by date_to
        if ($request->has('date_to') && $request->date_to) {
            $dateTo = $request->input('date_to');
            
            $query->whereHas('latestArchive', function($q) use ($dateTo) {
                $q->whereDate('archived_at', '<=', $dateTo);
            });
        }

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
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        $obrRequest = ObrRequest::with([
            'obrObjects',
            'paoRequest.officeCode',
            'paoRequest.officeCodeBudget.annualBudget',
            'latestStatus.internalStep',
            'archives.archivedByUser',
            'latestArchive.archivedByUser',
            'rejections.rejectedByUser'
        ])->findOrFail($id);
        
        return response()->json($obrRequest);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $obrRequest = ObrRequest::findOrFail($id);
            
            // Capture original data before deletion
            $original = [
                'id' => $obrRequest->id,
                'obr_no' => $obrRequest->obr_no,
                'office_address' => $obrRequest->office_address,
                'is_archived' => $obrRequest->is_archived,
                'pao_request_id' => $obrRequest->pao_request_id,
            ];
            
            // Delete all related records
            $obrRequest->obrObjects()->delete();
            $obrRequest->rejections()->delete();
            $obrRequest->archives()->delete();
            DB::table('paper_trail_statuses')->where('request_id', $obrRequest->id)->delete();
            
            // Log the permanent deletion
            DB::table('audit_logs')->insert([
                'auditable_id'   => $obrRequest->id,
                'auditable_type' => ObrRequest::class,
                'changes'        => json_encode([
                    'from' => $original,
                    'to'   => '[permanently deleted]',
                ]),
                'remarks'        => 'Permanently deleted archived OBR request and all related records',
                'updated_by'     => auth()->user()->employee_id ?? null,
                'updated_at'     => now(),
            ]);
            
            // Permanently delete
            $obrRequest->forceDelete();

            DB::commit();

            return response()->json([
                'message' => 'OBR permanently deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete archived OBR',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of users who have archived OBRs
     */
    public function getArchivers(Request $request): JsonResponse
    {
        $archivers = ObrRequestArchive::select('archived_by', 'users.name as archiver_name')
            ->join('users', 'obr_request_archives.archived_by', '=', 'users.id')
            ->distinct()
            ->get();

        return response()->json($archivers);
    }
}