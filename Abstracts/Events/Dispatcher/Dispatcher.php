<?php

namespace Apiato\Core\Abstracts\Events\Dispatcher;

use Apiato\Core\Abstracts\Events\Interfaces\ShouldHandle;
use Apiato\Core\Abstracts\Events\Interfaces\ShouldHandleNow;
use Apiato\Core\Abstracts\Events\Jobs\EventJob;
use Illuminate\Events\Dispatcher as EventDispatcher;
use Illuminate\Foundation\Bus\PendingDispatch as JobDispatcher;

/**
 * Created by PhpStorm.
 * User: arthur Devious
 */
class Dispatcher extends EventDispatcher
{
  public function dispatch($event, $payload = [], $halt = false)
  {
    if ($event instanceof ShouldHandle) {
      $job = new EventJob($event);
      $delay = $event->jobDelay ?? 0;
      (new JobDispatcher($job))
        ->delay($delay)
        ->onQueue($event->jobQueue);
    } else if ($event instanceof ShouldHandleNow) {
      $event->handle();
    }
    return parent::dispatch($event, $payload, $halt);
  }
}
