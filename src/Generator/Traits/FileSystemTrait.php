<?php

namespace Apiato\Core\Generator\Traits;

trait FileSystemTrait
{
    public function generateFile(string $path, string $content): void
    {
        $fullFilePath = $this->getFullFilePath($path);
        $fileName = basename($fullFilePath);
        if ($this->fileAlreadyExists($fullFilePath)) {
            $this->outputError("$fileName already exists");
        } else {
            $created = $this->fileSystem->put($fullFilePath, $content);

            if ($created) {
                $this->outputInfo("$fileName generated successfully.");
            } else {
                $this->outputError("$fileName could not be created");
            }
        }
    }

    /**
     * If path is for a directory, create it otherwise do nothing.
     */
    private function createDirectory($path): void
    {
        if ($this->fileAlreadyExists($path)) {
            return;
        }

        try {
            if (!$this->fileSystem->isDirectory(dirname($path))) {
                $this->fileSystem->makeDirectory(dirname($path), 0777, true, true);
            }
        } catch (\Exception $e) {
            $this->outputError('Could not create ' . $path);
        }
    }

    private function getFullFilePath($path): string
    {
        // Complete the missing parts of the path
        $path = base_path() . '/' .
            str_replace('\\', '/', self::ROOT . '/' . $path);

        // Try to create directory
        $this->createDirectory($path);

        // Return full path
        return $path;
    }

    private function fileAlreadyExists($path): bool
    {
        return $this->fileSystem->exists($path);
    }
}
