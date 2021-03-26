<?php

namespace Apiato\Core\Abstracts\Events\Jobs;

use Apiato\Core\Abstracts\Events\Interfaces\ShouldHandle;
use Apiato\Core\Abstracts\Jobs\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EventJob extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ShouldHandle $handler;

    public function __construct(ShouldHandle $handler)
    {
        $this->handler = $handler;
    }

    public function handle()
    {
        $this->handler->handle();
    }
}
