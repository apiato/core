<?php

declare(strict_types=1);

namespace Apiato\Generator\Traits;

use Throwable;

trait FileSystemTrait
{
    public function generateFile(string $filePath, string $stubContent): int|bool
    {
        return $this->fileSystem->put($filePath, $stubContent);
    }

    /**
     * If path is for a directory, create it otherwise do nothing.
     */
    public function createDirectory(string $path): void
    {
        if ($this->alreadyExists($path)) {
            $this->printErrorMessage($this->fileType . ' already exists');

            // The file does exist - return but NOT exit.
            return;
        }

        try {
            if (!$this->fileSystem->isDirectory(\dirname($path))) {
                $this->fileSystem->makeDirectory(\dirname($path), 0777, true, true);
            }
        } catch (Throwable) {
            $this->printErrorMessage(\sprintf('Could not create %s', $path));
        }
    }

    /**
     * Determine if the file already exists.
     */
    protected function alreadyExists(string $path): bool
    {
        return $this->fileSystem->exists($path);
    }
}
