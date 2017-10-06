<?php

namespace Apiato\Core\Abstracts\Commands;

use Apiato\Core\Traits\CallableTrait;

/**
 * Class Call
 *
 * This class is created as workaround for the following issue:
 * If we try to use `CallableTrait` in the `Apiato\Core\Abstracts\Commands\ConsoleCommand`
 * we get conflict between the trait function call and the parent class `Illuminate\Console\Command`
 * extended by `ConsoleCommand`, as the function `call` is used in both classes.
 * Renaming the function in the trait doesn't work and we keep getting the interface incompatibility error,
 * between both functions signatures.
 *
 * @author  Mahmoud Zalt  <mahmoud@zalt.me>
 */
class Call
{

    use CallableTrait;
}
