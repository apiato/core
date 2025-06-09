<?php

declare(strict_types=1);

namespace Apiato\Generator\Traits;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;

trait PrinterTrait
{
    public function printStartedMessage(string $containerName, string $fileName): void
    {
        $this->printInfoMessage('> Generating (' . $fileName . ') in (' . $containerName . ') Container.');
    }

    public function printInfoMessage(string $message): void
    {
        info($message);
    }

    public function printFinishedMessage(string $type): void
    {
        $this->printInfoMessage($type . ' generated successfully.');
    }

    public function printErrorMessage(string $message): void
    {
        error($message);
    }
}
