<?php

namespace Apiato\Core\Generator\Commands\TestCases;

use Apiato\Core\Generator\CompositeGeneratorCommand;

class TestCasesGenerator extends CompositeGeneratorCommand
{
    public static function getCommandName(): string
    {
        return 'apiato:make:testcase';
    }

    public static function getCommandDescription(): string
    {
        return 'Create all TestCase files (Unit, Functional, Api, Cli)';
    }

    protected static function getCustomCommandArguments(): array
    {
        return [];
    }

    protected function askCustomInputs(): void
    {
    }

    public function runGeneratorCommands(): void
    {
        $this->runGeneratorCommand(ContainerTestCaseGenerator::class, [
            '--section' => $this->sectionName,
            '--container' => $this->containerName,
        ]);

        $this->runGeneratorCommand(UnitTestCaseGenerator::class, [
            '--section' => $this->sectionName,
            '--container' => $this->containerName,
        ]);

        $this->runGeneratorCommand(FunctionalTestCaseGenerator::class, [
            '--section' => $this->sectionName,
            '--container' => $this->containerName,
        ]);

        $this->runGeneratorCommand(ApiTestCaseGenerator::class, [
            '--section' => $this->sectionName,
            '--container' => $this->containerName,
        ]);

        $this->runGeneratorCommand(WebTestCaseGenerator::class, [
            '--section' => $this->sectionName,
            '--container' => $this->containerName,
        ]);

        $this->runGeneratorCommand(CliTestCaseGenerator::class, [
            '--section' => $this->sectionName,
            '--container' => $this->containerName,
        ]);
    }
}
