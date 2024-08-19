<?php

namespace Apiato\Core\Generator;

use Apiato\Core\Foundation\Facades\Apiato;
use Apiato\Core\Generator\Traits\ConsoleCommandArgumentsTrait;
use Apiato\Core\Generator\Traits\ConsoleInputTrait;
use Apiato\Core\Generator\Traits\ConsoleOutputTrait;
use Apiato\Core\Generator\Traits\FileSystemTrait;
use Apiato\Core\Generator\Traits\FormatterTrait;
use Apiato\Core\Generator\Traits\ParserTrait;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem as IlluminateFilesystem;
use Illuminate\Support\Str;

abstract class GeneratorCommand extends Command
{
    use ParserTrait;
    use ConsoleInputTrait;
    use ConsoleOutputTrait;
    use FileSystemTrait;
    use FormatterTrait;
    use ConsoleCommandArgumentsTrait;

    /**
     * Root directory of all sections.
     *
     * @var string
     */
    private const ROOT = 'app/Containers';

    /**
     * Relative path for the stubs (relative to this directory / file).
     *
     * @var string
     */
    private const STUB_PATH = 'Stubs/*';

    /**
     * Relative path for the custom stubs (relative to the app/Ship directory!).
     */
    private const CUSTOM_STUB_PATH = 'Generators/CustomStubs/*';

    private const DEFAULT_SECTION_NAME = 'AppSection';

    protected string $sectionName;

    protected string $containerName;

    protected string $fileName;

    protected bool $allowSpecialCharactersInFileName = true;

    public function __construct(
        protected IlluminateFilesystem $fileSystem,
    ) {
        $this->name = $this->getCommandName();
        $this->description = $this->getCommandDescription();
        parent::__construct();
    }

    abstract public static function getCommandName(): string;

    abstract public static function getCommandDescription(): string;

    abstract public static function getFileType(): string;

    /**
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $this->askGeneralInputs();

        $this->askCustomInputs();

        $this->generateFile();
    }

    public function getDefaultFileName(): string
    {
        return 'Default' . $this->getFileTypeCapitalized();
    }

    protected function askGeneralInputs(): void
    {
        $this->askSection();
        $this->askContainer();
        $this->askFileName();
    }

    protected function askSection(): void
    {
        $input = $this->checkParameterOrAskTextSuggested(
            param: 'section',
            label: 'Select the section:',
            default: self::DEFAULT_SECTION_NAME,
            suggestions: Apiato::getSectionNames(),
        );

        $this->sectionName = Str::ucfirst($input);
        $this->sectionName = $this->removeSpecialChars($this->sectionName);
    }

    protected function askContainer(): void
    {
        $input = $this->checkParameterOrAskTextSuggested(
            param: 'container',
            label: 'Select the container:',
            suggestions: Apiato::getSectionContainerNames($this->sectionName),
        );

        $this->containerName = Str::ucfirst($input);
        $this->containerName = $this->removeSpecialChars($this->containerName);
    }

    protected function askFileName(): void
    {
        $input = $this->checkParameterOrAskText(
            param: 'file',
            label: 'Enter the name of the ' . $this->getFileType() . ' file:',
            default: $this->getDefaultFileName(),
        );

        if ($this->allowSpecialCharactersInFileName) {
            $this->fileName = $input;
        } else {
            $this->fileName = $this->removeSpecialChars($input);
        }
    }

    abstract protected function askCustomInputs(): void;

    abstract protected function getFilePath(): string;

    abstract protected function getStubFileName(): string;

    abstract protected function getStubParameters(): array;
}
