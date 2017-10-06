<?php

namespace Apiato\Core\Abstracts\Commands;

use Illuminate\Console\Command as LaravelCommand;
use Illuminate\Support\Facades\App;

/**
 * Class ConsoleCommand
 *
 * @author  Mahmoud Zalt  <mahmoud@zalt.me>
 */
abstract class ConsoleCommand extends LaravelCommand
{

    public function apiatoCall($class, $runArguments = [], $methods = [])
    {
        return App::make(Call::class)->call($class, $runArguments, $methods);
    }
}
