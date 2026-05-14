<?php

namespace App\Notifications;

use App\Models\InvoiceViewLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceViewedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public InvoiceViewLog $log,
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
        $invoice = $this->log->invoice;

        return (new MailMessage)
            ->subject("Invoice Viewed: {$invoice->title}")
            ->line("The invoice **{$invoice->title}** was just viewed.")
            ->line("**IP:** {$this->log->ip}")
            ->line('**Country:** '.($this->log->country ?? 'Unknown'))
            ->line("**Browser:** {$this->log->browser}")
            ->line("**Time:** {$this->log->viewed_at->format('d F, Y h:i a')}")
            ->action('View Invoice', url("/admin/invoices/{$invoice->id}/edit"));
    }
}
