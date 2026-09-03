<?php

namespace App\Jobs;

use App\Support\TeacherStatusWhatsappNotifier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTeacherStatusNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(public int $logId)
    {
        $this->onQueue('notifications');
    }

    public function handle(TeacherStatusWhatsappNotifier $notifier): void
    {
        $notifier->deliverLog($this->logId);
    }
}
