<?php

namespace Apiato\Core\Abstracts\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

// TODO: BC
// remove "implements ShouldQueue"
// from here and add let the child classes decide if they want to use the queue or not
// Also maybe move trait to the child classes, so they can decide if they want to use it or not?
abstract class Job implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
}
