<?php

namespace App\Services;

use App\Models\ServiceRequest;
use App\Models\ServiceRequestChange;
use App\Models\User;
use App\Events\ServiceRequestStatusChanged;
use App\Events\AdditionalInfoRequested;
use App\Notifications\ServiceRequestStatusNotification;
use App\Notifications\AdditionalInfoNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;


class ServiceRequestService
{
    /**
     * Allowed status transitions
     */
    protected array $allowedTransitions = [
        'pending' => ['info_requested', 'approved', 'rejected'],
        'info_requested' => ['pending', 'approved', 'rejected'],
        'approved' => [],
        'rejected' => [],
    ];

    /**
     * Submit a new service request by customer
     */

    public function createForCustomer(array $data, User $user): ServiceRequest
    {
        return ServiceRequest::create([
            'user_id'         => $user->id,
            'service_type_id'=> $data['service_type_id'],
            'status'          => 'pending',
            'submitted_at'    => now(),
            'remark'          => $data['remark'] ?? null,
        ]);
    }
    
    public function submit(ServiceRequest $serviceRequest): ServiceRequest
    {
        $serviceRequest->status = 'pending';
        $serviceRequest->submitted_at = Carbon::now();
        $serviceRequest->save();

        return $serviceRequest;
    }

    /**
     * Approve a service request (staff only)
     */
    public function approve(ServiceRequest $serviceRequest, User $staff, ?string $remark = null): ServiceRequest
    {
        return DB::transaction(function () use ($serviceRequest, $staff, $remark) {
            $this->assertCanTransition($serviceRequest, 'approved');

            $oldStatus = $serviceRequest->status;

            $serviceRequest->update([
                'status' => 'approved',
                'remark' => $remark,
            ]);

            $this->logChange($serviceRequest, 'status', $oldStatus, 'approved', $staff, $remark);

            event(new ServiceRequestStatusChanged($serviceRequest, $oldStatus, 'approved'));
            Notification::send($serviceRequest->user, new ServiceRequestStatusNotification($serviceRequest, $oldStatus, 'approved'));

            return $serviceRequest;
        });
    }

    /**
     * Reject a service request (staff only)
     */
    public function reject(ServiceRequest $serviceRequest, User $staff, ?string $remark = null): ServiceRequest
    {
        return DB::transaction(function () use ($serviceRequest, $staff, $remark) {
            $this->assertCanTransition($serviceRequest, 'rejected');

            $oldStatus = $serviceRequest->status;

            $serviceRequest->update([
                'status' => 'rejected',
                'remark' => $remark,
            ]);

            $this->logChange($serviceRequest, 'status', $oldStatus, 'rejected', $staff, $remark);

            event(new ServiceRequestStatusChanged($serviceRequest, $oldStatus, 'rejected'));
            Notification::send($serviceRequest->user, new ServiceRequestStatusNotification($serviceRequest, $oldStatus, 'rejected'));

            return $serviceRequest;
        });
    }

    /**
     * Request additional information from customer
     */
    public function requestInfo(ServiceRequest $serviceRequest, User $staff, ?string $remark = null): ServiceRequest
    {
        return DB::transaction(function () use ($serviceRequest, $staff, $remark) {
            $this->assertCanTransition($serviceRequest, 'info_requested');

            $oldStatus = $serviceRequest->status;

            $serviceRequest->update([
                'status' => 'info_requested',
                'remark' => $remark,
            ]);

            $this->logChange($serviceRequest, 'status', $oldStatus, 'info_requested', $staff, $remark);

            event(new AdditionalInfoRequested($serviceRequest, $remark));
            Notification::send($serviceRequest->user, new AdditionalInfoNotification($serviceRequest, $remark));

            return $serviceRequest;
        });
    }

    /**
     * Change service type (staff only)
     */
    public function changeServiceType(ServiceRequest $serviceRequest, int $newServiceTypeId, User $staff, ?string $remark = null): ServiceRequest
    {
        return DB::transaction(function () use ($serviceRequest, $newServiceTypeId, $staff, $remark) {
            if (in_array($serviceRequest->status, ['approved', 'rejected'])) {
                throw ValidationException::withMessages([
                    'status' => 'Cannot change service type after approval or rejection.'
                ]);
            }

            $oldServiceTypeId = $serviceRequest->service_type_id;

            if ($oldServiceTypeId == $newServiceTypeId) {
                return $serviceRequest;
            }

            $serviceRequest->update([
                'service_type_id' => $newServiceTypeId,
            ]);

            $this->logChange(
                $serviceRequest,
                'service_type_id',
                (string) $oldServiceTypeId,
                (string) $newServiceTypeId,
                $staff,
                $remark
            );

            return $serviceRequest;
        });
    }

    /**
     * Assert status transition validity
     */
    protected function assertCanTransition(ServiceRequest $serviceRequest, string $toStatus): void
    {
        $fromStatus = $serviceRequest->status;

        if (! in_array($toStatus, $this->allowedTransitions[$fromStatus] ?? [])) {
            throw ValidationException::withMessages([
                'status' => "Cannot change status from {$fromStatus} to {$toStatus}."
            ]);
        }
    }

    /**
     * Centralized audit logging
     */
    protected function logChange(
        ServiceRequest $serviceRequest,
        ?string $fieldName,
        ?string $oldValue,
        ?string $newValue,
        User $staff,
        ?string $remark
    ): void {
        ServiceRequestChange::create([
            'service_request_id' => $serviceRequest->id,
            'field_name' => $fieldName,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'changed_by' => $staff->id,
            'remark' => $remark,
        ]);
    }
}
