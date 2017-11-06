<?php
/**
 * Author: Arthur Devious
 */

namespace Apiato\Core\Abstracts\Events\Jobs;

use Apiato\Core\Abstracts\Events\Interfaces\ShouldHandle;

class EventJob
{
    public $handler;

    /**
     * EventJob constructor.
     */
    public function __construct(ShouldHandle $handler)
    {
        $this->handler = $handler;
    }

    public function handle()
    {
        $this->handler->handle();
    }
}