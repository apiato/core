<?php

namespace Apiato\Core\Abstracts\Events;

use Apiato\Core\Abstracts\Events\Interfaces\ShouldHandle;
use Apiato\Core\Abstracts\Events\Interfaces\ShouldHandleNow;
use Apiato\Core\Abstracts\Events\Jobs\EventJob;
use Illuminate\Foundation\Bus\PendingDispatch as Dispatcher;

/**
 * Class Event
 *
 * @author  Arthur Devious
 */
abstract class Event
{

    /**
     * Event constructor.
     */
    public function __construct()
    {
        if ($this instanceof ShouldHandleNow) {
            $this->handle();
        } elseif ($this instanceof ShouldHandle) {
            new Dispatcher(new EventJob($this));
        }
    }
}
