<?php

namespace Apiato\Core\Generator\Traits;

use Closure;
use Laravel\Prompts\Progress;
use function Laravel\Prompts\alert;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\progress;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\table;
use function Laravel\Prompts\warning;

trait ConsoleOutputTrait
{
    public function outputNote(string $message): void
    {
        note($message);
    }

    public function outputInfo(string $message): void
    {
        info($message);
    }

    public function outputWarning(string $message): void
    {
        warning($message);
    }

    public function outputError(string $message): void
    {
        error($message);
    }

    public function outputAlert(string $message): void
    {
        alert($message);
    }

    public function outputTable(array $headers, array $rows): void
    {
        table($headers, $rows);
    }

    public function outputSpin(Closure $callback, string $message = ''): void
    {
        spin($callback, $message);
    }

    public function outputProgress(string $label, iterable|int $steps, ?Closure $callback = null, string $hint = ''): array|Progress
    {
        return progress(
            label: $label,
            steps: $steps,
            callback: $callback,
            hint: $hint,
        );
    }
}
