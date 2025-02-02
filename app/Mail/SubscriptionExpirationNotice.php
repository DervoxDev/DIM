<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Subscription;
use Carbon\Carbon;

class SubscriptionExpirationNotice extends Mailable
{
    use Queueable, SerializesModels;

    public $subscription;
    public $daysRemaining;

    public function __construct(Subscription $subscription, int $daysRemaining)
    {
        $this->subscription = $subscription;
        $this->daysRemaining = $daysRemaining;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Subscription Expiration Notice - {$this->daysRemaining} Days Remaining",
        );
    }

    public function content(): Content
    {
        // Ensure we're using the same days calculation
        $daysRemaining = Carbon::now()->startOfDay()->diffInDays(
            Carbon::parse($this->subscription->subscription_expiredDate)->startOfDay(),
            false
        );

        return new Content(
            view: 'emails.subscription_expiration',
            with: [
                'teamName' => $this->subscription->team->name,
                'expirationDate' => $this->subscription->subscription_expiredDate->format('Y-m-d'),
                'daysRemaining' => $daysRemaining,
                'planName' => $this->subscription->plan->name,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
