<?php

namespace App\Notifications;

use App\Models\Payment;
use App\Services\Helper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentRecordedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Payment $payment,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $invoice = $this->payment->invoice;

        $mail = (new MailMessage)
            ->subject("Payment updated for {$invoice->title}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("**Invoice No:** {$invoice->invoice_no}")
            ->line("**Invoice Title:** {$invoice->title}");

        if ($this->payment->title) {
            $mail->line("**Payment Title:** {$this->payment->title}");
        }

        return $mail
            ->line('**Amount:** '.Helper::moneyFormat($this->payment->amount))
            ->line("**Status:** {$this->payment->status->label()}")
            ->line("**Method:** {$this->payment->payment_method->label()}")
            ->action('View Invoice', $invoice->public_url);
    }
}
