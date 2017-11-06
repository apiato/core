<?php

namespace Apiato\Core\Abstracts\Events;

use Apiato\Core\Abstracts\Events\Jobs\EventJob;
use Apiato\Core\Abstracts\Events\Interfaces\ShouldHandle;
use Apiato\Core\Abstracts\Events\Interfaces\ShouldHandleNow;
use Illuminate\Foundation\Bus\PendingDispatch as Dispatcher;

/**
 * Class Event.
 *
 * Author: Arthur Devious
 */
abstract class Event
{
    public function __construct()
    {
        if ($this instanceof ShouldHandleNow) {
            $this->handle();
        } elseif ($this instanceof ShouldHandle) {
            new Dispatcher(new EventJob($this));
        }
    }
}
