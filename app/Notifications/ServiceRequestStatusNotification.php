<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceRequestStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public ServiceRequest $serviceRequest;
    public string $oldStatus;
    public string $newStatus;

    public function __construct(ServiceRequest $serviceRequest, string $oldStatus, string $newStatus)
    {
        $this->serviceRequest = $serviceRequest;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject("Application Status Updated")
                    ->line("Your application (#{$this->serviceRequest->id}) status changed from {$this->oldStatus} to {$this->newStatus}.")
                    ->action('View Application', url("/customer/service-requests/{$this->serviceRequest->id}"));
    }

    public function toDatabase($notifiable)
    {
        return [
            'service_request_id' => $this->serviceRequest->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'message' => "Your application status changed from {$this->oldStatus} to {$this->newStatus}.",
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
