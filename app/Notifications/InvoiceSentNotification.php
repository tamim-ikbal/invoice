<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Services\Helper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceSentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Invoice $invoice,
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
        return (new MailMessage)
            ->subject("Invoice #{$this->invoice->invoice_no}: {$this->invoice->title}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("**Invoice No:** {$this->invoice->invoice_no}")
            ->line("**Invoice Title:** {$this->invoice->title}")
            ->line('**Date:** '.Helper::dateFormat($this->invoice->created_at))
            ->action('View Invoice', $this->invoice->public_url);
    }
}
