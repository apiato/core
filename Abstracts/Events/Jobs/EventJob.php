<?php

namespace Apiato\Core\Abstracts\Events\Jobs;

use Apiato\Core\Abstracts\Events\Interfaces\ShouldHandle;
use Apiato\Core\Abstracts\Jobs\Job;
use Illuminate\Contracts\Queue\ShouldQueue;

class EventJob extends Job implements ShouldQueue
{
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
