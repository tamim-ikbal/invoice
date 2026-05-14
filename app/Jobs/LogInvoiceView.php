<?php

namespace App\Jobs;

use App\Models\InvoiceViewLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class LogInvoiceView implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $invoiceId,
        public string $ip,
        public string $browser,
        public string $viewedAt,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $location = $this->resolveLocation();

        InvoiceViewLog::create([
            'invoice_id' => $this->invoiceId,
            'ip' => $this->ip,
            'browser' => $this->browser,
            'country' => $location,
            'viewed_at' => $this->viewedAt,
        ]);
    }

    /**
     * Resolve geolocation from IP using ipinfo.io Lite API.
     * Skips private/localhost IPs.
     */
    private function resolveLocation(): ?string
    {
        if ($this->isPrivateIp($this->ip)) {
            return null;
        }

        try {
            $response = Http::timeout(5)
                ->withToken(config('services.ipinfo.token'))
                ->get("https://api.ipinfo.io/lite/{$this->ip}");

            $data = $response->json();

            if (! empty($data['country'])) {
                return $data['country'];
            }
        } catch (\Throwable) {
            // Silently fail — location is optional
        }

        return null;
    }

    /**
     * Check if the IP is a private or localhost address.
     */
    private function isPrivateIp(string $ip): bool
    {
        return in_array($ip, ['127.0.0.1', '::1'])
            || str_starts_with($ip, '192.168.')
            || str_starts_with($ip, '10.')
            || str_starts_with($ip, '172.16.');
    }
}
