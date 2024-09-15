<?php

namespace Apiato\Core\Generator\Traits;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Symfony\Component\Yaml\Yaml;

trait FileSystemTrait
{
    /**
     * @throws FileNotFoundException
     */
    public function generateFile(): void
    {
        $fullFilePath = $this->getFullFilePath($this->getFilePath());
        if ($this->fileAlreadyExists($fullFilePath)) {
            $this->outputError($this->getFileTypeCapitalized() . ' already exists');
        } else {
            $renderedStubContent = $this->parseStubContent($this->getStubFileContent(), $this->getStubParameters());

            $created = $this->fileSystem->put($fullFilePath, $renderedStubContent);

            if ($created) {
                $this->outputInfo($this->getFileTypeCapitalized() . ' generated successfully.');
            } else {
                $this->outputError($this->getFileTypeCapitalized() . ' could not be created');
            }
        }
    }

    /**
     * If path is for a directory, create it otherwise do nothing.
     */
    public function createDirectory($path): void
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

    /**
     * @throws FileNotFoundException
     */
    protected function getStubFileContent(): string
    {
        // Check if there is a custom stub file in Ship that overrides the default stub on Core
        $path = app_path() . '/Ship/' . self::CUSTOM_STUB_PATH;
        $file = str_replace('*', $this->getStubFileName(), $path);

        // Check if the custom file exists
        if (!$this->fileSystem->exists($file)) {
            // It does not exist - so take the default file!
            $path = __DIR__ . '/../' . self::STUB_PATH;
            $file = str_replace('*', $this->getStubFileName(), $path);
        }

        // Now load the stub
        return $this->fileSystem->get($file);
    }

    protected function fileAlreadyExists($path): bool
    {
        return $this->fileSystem->exists($path);
    }

    protected function readYamlConfig(string $filePath, ?array $default = null): array
    {
        if (!file_exists($filePath)) {
            if (is_null($default)){
                throw new \RuntimeException("Configuration file not found: $filePath");
            }else{
                return $default;
            }
        }

        return Yaml::parseFile($filePath);
    }
}
