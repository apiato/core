<?php

namespace Apiato\Generator\Traits;

use function Laravel\Prompts\info;
use function Laravel\Prompts\error;

trait PrinterTrait
{
    public function printStartedMessage(string $containerName, string $fileName): void
    {
        $this->printInfoMessage('> Generating (' . $fileName . ') in (' . $containerName . ') Container.');
    }

    public function printInfoMessage($message): void
    {
        info($message);
    }

    public function printFinishedMessage(string $type): void
    {
        $this->printInfoMessage($type . ' generated successfully.');
    }

    public function printErrorMessage($message): void
    {
        error($message);
    }
}
