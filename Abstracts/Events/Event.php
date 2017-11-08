<?php

namespace Apiato\Core\Abstracts\Events;

use Apiato\Core\Abstracts\Events\Interfaces\ShouldHandle;
use Apiato\Core\Abstracts\Events\Interfaces\ShouldHandleNow;
use Apiato\Core\Abstracts\Events\Jobs\EventJob;
use Apiato\Core\Abstracts\Events\Traits\JobProperties;
use Illuminate\Foundation\Bus\PendingDispatch as Dispatcher;

/**
 * Class Event
 *
 * @author  Arthur Devious
 */
abstract class Event
{
    use JobProperties;
}
