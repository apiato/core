<?php

namespace Apiato\Core\Generator\Traits;

use Symfony\Component\Yaml\Yaml;

trait FileSystemTrait
{
    public function generateFile(string $path, string $content): void
    {
        $fullFilePath = $this->getFullFilePath($path);
        $fileName = basename($fullFilePath);

        if ($this->overrideExistingFile) {
            // If the file already exists, replace it
            if ($this->fileAlreadyExists($fullFilePath)) {
                $this->fileSystem->replace($fullFilePath, $content);
                $this->outputInfo("$fileName modified successfully.");
            } else {
                // If the file does not exist, create it
                $created = $this->fileSystem->put($fullFilePath, $content);

                if ($created) {
                    $this->outputInfo("$fileName generated successfully.");
                } else {
                    $this->outputError("$fileName could not be created");
                }
            }
        } else {
            // If the file exists, show an error
            if ($this->fileAlreadyExists($fullFilePath)) {
                $this->outputError("$fileName already exists");
            } else {
                // If the file does not exist, create it
                $created = $this->fileSystem->put($fullFilePath, $content);

                if ($created) {
                    $this->outputInfo("$fileName generated successfully.");
                } else {
                    $this->outputError("$fileName could not be created");
                }
            }
        }
    }

    protected function getFullFilePath($path): string
    {
        // Complete the missing parts of the path
        $path = base_path() . '/' .
            str_replace('\\', '/', self::ROOT . '/' . $path);

        // Try to create directory
        $this->createDirectory($path);

        // Return full path
        return $path;
    }

    protected function fileAlreadyExists($path): bool
    {
        return $this->fileSystem->exists($path);
    }

    protected function readYamlConfig(string $filePath, array|null $default = null): array
    {
        if (!file_exists($filePath)) {
            if (is_null($default)) {
                throw new \RuntimeException("Configuration file not found: $filePath");
            } else {
                return $default;
            }
        }

        $config = Yaml::parseFile($filePath);

        return $config ?? [];
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
}
