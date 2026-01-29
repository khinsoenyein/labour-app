<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequestRequest;
use App\Http\Requests\UpdateServiceRequestRequest;
use App\Models\ServiceRequest;
use App\Services\ServiceRequestService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * ===============================
 * Customer Service Request API
 * ===============================
 */
class CustomerServiceRequestController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected ServiceRequestService $service
    ) {}


    /**
     * List customer applications
     */
    public function index(Request $request): JsonResponse
    {
        $applications = ServiceRequest::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);


        return response()->json($applications);
    }


    /**
     * Submit new application
     */
    public function store(StoreServiceRequestRequest $request): JsonResponse
    {
        $application = $this->service->createForCustomer(
            $request->validated(),
            $request->user()
        );


        return response()->json([
            'message' => 'Application submitted successfully',
            'data' => $application,
        ], 201);
    }


    /**
     * View application details
     */
    public function show(ServiceRequest $serviceRequest): JsonResponse
    {
        $this->authorize('view', $serviceRequest);

        return response()->json($serviceRequest->load('documents', 'service'));
    }
}
