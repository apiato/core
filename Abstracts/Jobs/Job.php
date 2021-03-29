<?php

namespace Apiato\Core\Abstracts\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class Job
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
}
