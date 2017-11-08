<?php

namespace Apiato\Core\Abstracts\Events\Jobs;

use Apiato\Core\Abstracts\Events\Interfaces\ShouldHandle;

/**
 * Class EventJob
 *
 * @author  Arthur Devious
 */
class EventJob
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
     * @void
     */
    public function handle()
    {
        $this->handler->handle();
    }
}
