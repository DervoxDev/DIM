<?php
    namespace App\Mail;

    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Mail\Mailable;
    use Illuminate\Mail\Mailables\Content;
    use Illuminate\Mail\Mailables\Envelope;
    use Illuminate\Queue\SerializesModels;
    use App\Models\Subscription;

    class SubscriptionExpiredNotice extends Mailable
    {
        use Queueable, SerializesModels;

        public $subscription;

        /**
         * Create a new message instance.
         */
        public function __construct(Subscription $subscription)
        {
            $this->subscription = $subscription;
        }

        /**
         * Get the message envelope.
         */
        public function envelope(): Envelope
        {
            return new Envelope(
                subject: 'Your Subscription Has Expired',
            );
        }

        /**
         * Get the message content definition.
         */
        public function content(): Content
        {
            return new Content(
                view: 'emails.subscription_expired',
                with: [
                    'teamName' => $this->subscription->team->name,
                    'expirationDate' => $this->subscription->subscription_expiredDate->format('Y-m-d'),
                    'planName' => $this->subscription->plan->name,
                ]
            );
        }

        /**
         * Get the attachments for the message.
         *
         * @return array<int, \Illuminate\Mail\Mailables\Attachment>
         */
        public function attachments(): array
        {
            return [];
        }
    }
