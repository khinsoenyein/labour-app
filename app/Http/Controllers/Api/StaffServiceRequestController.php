<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequestRequest;
use App\Http\Requests\UpdateServiceRequestRequest;
use App\Models\ServiceRequest;
use App\Services\ServiceRequestService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StaffServiceRequestController extends Controller
{
    public function __construct(
        protected ServiceRequestService $service
    ) {}


    /**
     * List pending applications
     */
    public function pending(): JsonResponse
    {
        $applications = ServiceRequest::whereIn('status', ['pending', 'info_requested'])
            ->latest()
            ->paginate(20);


        return response()->json($applications);
    }


    /**
     * Approve application
     */
    public function approve(ServiceRequest $serviceRequest, Request $request): JsonResponse
    {
        $this->service->approve($serviceRequest, $request->user()->id);


        return response()->json([
            'message' => 'Application approved successfully',
        ]);
    }


    /**
     * Reject application
     */
    public function reject(ServiceRequest $serviceRequest, Request $request): JsonResponse
    {
        $this->service->reject(
            $serviceRequest,
            $request->user()->id,
            $request->input('reason')
        );


        return response()->json([
            'message' => 'Application rejected',
        ]);
    }


    /**
     * Request additional information
     */
    public function requestInfo(ServiceRequest $serviceRequest, Request $request): JsonResponse
    {
        $this->service->requestInfo(
            $serviceRequest,
            $request->user()->id,
            $request->input('message')
        );


        return response()->json([
            'message' => 'Additional information requested',
        ]);
    }
}
