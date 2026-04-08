<?php

namespace App\Jobs;

use App\Models\PrintJob;
use App\Services\Printing\PrintManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessPrintJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public array $backoff = [5, 15, 30];

    public function __construct(public int $printJobId) {}

    public function handle(PrintManager $printManager): void
    {
        $printJob = PrintJob::find($this->printJobId);

        if (! $printJob) {
            return;
        }

        $printJob->update([
            'status' => 'processing',
            'attempts' => $this->attempts(),
            'error_message' => null,
        ]);

        $printManager->process($printJob);
    }

    public function failed(Throwable $exception): void
    {
        $printJob = PrintJob::find($this->printJobId);

        if (! $printJob) {
            return;
        }

        $printJob->update([
            'status' => 'failed',
            'attempts' => max($printJob->attempts, $this->attempts()),
            'error_message' => $exception->getMessage(),
        ]);
    }
}
