<?php

namespace Apiato\Core\Generator;

use Apiato\Core\Generator\Traits\FileSystemTrait;
use Illuminate\Filesystem\Filesystem as IlluminateFilesystem;

abstract class FileGeneratorCommand extends GeneratorCommand
{
    use FileSystemTrait;

    /**
     * Root directory of all sections.
     *
     * @var string
     */
    protected const ROOT = 'app/Containers';

    protected string|null $fileName = null;

    protected bool $overrideExistingFile = false;

    protected bool $allowSpecialCharactersInFileName = true;

    public function __construct(
        protected IlluminateFilesystem $fileSystem,
    ) {
        parent::__construct();
    }

    abstract public static function getFileType(): string;

    public function handle(): void
    {
        parent::handle();

        $this->askSection();

        $this->askContainer();

        $this->askCustomInputs();

        $this->askFileName();

        $this->askForTest();

        $this->runGeneratorCommands();
    }

    public function getDefaultFileName(): string
    {
        return 'Default' . $this->getFileTypeCapitalized();
    }

    protected function askFileName(): void
    {
        if ($this->fileName) {
            return;
        }

        $input = $this->checkParameterOrAskText(
            param: 'file',
            label: 'Enter the name of the ' . $this->getFileTypeCapitalized() . ' file:',
            default: $this->getDefaultFileName(),
        );

        if ($this->allowSpecialCharactersInFileName) {
            $this->fileName = $input;
        } else {
            $this->fileName = $this->removeSpecialChars($input);
        }
    }

    protected function runGeneratorCommands(): void
    {
        $this->generateFile($this->getFilePath(), $this->getFileContent());
        if ($this->isTestable() && $this->test) {
            $this->createTestCases();
            $this->generateFile($this->getTestPath(), $this->getTestContent());
        }
    }

    abstract protected function getFilePath(): string;

    abstract protected function getFileContent(): string;
}
