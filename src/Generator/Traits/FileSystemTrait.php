<?php

namespace Apiato\Generator\Traits;

trait FileSystemTrait
{
    public function generateFile($filePath, $stubContent): bool|int
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

            // the file does exist - return but NOT exit
            return;
        }

        try {
            if (!$this->fileSystem->isDirectory(dirname($path))) {
                $this->fileSystem->makeDirectory(dirname($path), 0777, true, true);
            }
        } catch (\Exception) {
            $this->printErrorMessage('Could not create ' . $path);
        }
    }

    /**
     * Determine if the file already exists.
     *
     * @return bool
     */
    protected function alreadyExists($path)
    {
        return $this->fileSystem->exists($path);
    }
}
