<?php

namespace App\Notifications;

use App\Models\BloodRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UrgentBloodRequestNotification extends Notification
{
    use Queueable;

    public function __construct(public BloodRequest $bloodRequest, public ?float $distanceKm = null)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $hospital = $this->bloodRequest->hospital;
        $url = route('requests.show', $this->bloodRequest);

        $message = (new MailMessage)
            ->subject('🩸 Urgent Blood Request — Your Help is Needed')
            ->greeting("Hello {$notifiable->full_name},")
            ->line("A hospital near you has just posted an urgent request for **{$this->bloodRequest->blood_type}** blood — your blood type matches.")
            ->line("**Hospital:** {$hospital->hospital_name}")
            ->line("**Location:** " . ($this->bloodRequest->location ?? $hospital->district));

        if ($this->distanceKm !== null) {
            $message->line(sprintf('**Distance from you:** %.1f km', $this->distanceKm));
        }

        return $message
            ->line("**Units needed:** {$this->bloodRequest->quantity_required}")
            ->line("**Urgency:** " . ucfirst($this->bloodRequest->urgency_level))
            ->action('View Request & Donate', $url)
            ->line('Every minute counts. Thank you for being a hero ❤️')
            ->salutation('— The BloodLine Team');
    }
}
