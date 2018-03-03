<?php

namespace Apiato\Core\Abstracts\Events\Jobs;

use Apiato\Core\Abstracts\Events\Interfaces\ShouldHandle;
use Apiato\Core\Abstracts\Jobs\Job;

/**
 * Class EventJob
 *
 * @author  Arthur Devious
 */
class EventJob extends Job
{
    public $handler;

    /**
     * EventJob constructor.
     *
     * @param \Apiato\Core\Abstracts\Events\Interfaces\ShouldHandle $handler
     */

    public function __construct(ShouldHandle $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Handle the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->handler->handle();
    }
}
