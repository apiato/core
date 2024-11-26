<?php

namespace Apiato\Core\Generator\Traits;

use Apiato\Core\Generator\Commands\TestCases\ApiTestCaseGenerator;
use Apiato\Core\Generator\Commands\TestCases\CliTestCaseGenerator;
use Apiato\Core\Generator\Commands\TestCases\FunctionalTestCaseGenerator;
use Apiato\Core\Generator\Commands\TestCases\UnitTestCaseGenerator;
use Apiato\Core\Generator\Commands\TestCases\WebTestCaseGenerator;
use Apiato\Core\Generator\ParentTestCase;

trait HasTestTrait
{
    abstract protected function getTestPath(): string;

    abstract protected function getTestContent(): string;

    abstract protected function getParentTestCase(): ParentTestCase;

    protected function createTestCases(): void
    {
        switch ($this->getParentTestCase()) {
            case ParentTestCase::UNIT_TEST_CASE:
                $this->runGeneratorCommand(UnitTestCaseGenerator::class, [
                    '--section' => $this->sectionName,
                    '--container' => $this->containerName,
                ], silent: true);
                break;
            case ParentTestCase::API_TEST_CASE:
                $this->runGeneratorCommand(ApiTestCaseGenerator::class, [
                    '--section' => $this->sectionName,
                    '--container' => $this->containerName,
                ], silent: true);
                break;
            case ParentTestCase::FUNCTIONAL_TEST_CASE:
                $this->runGeneratorCommand(FunctionalTestCaseGenerator::class, [
                    '--section' => $this->sectionName,
                    '--container' => $this->containerName,
                ], silent: true);
                break;
            case ParentTestCase::CLI_TEST_CASE:
                $this->runGeneratorCommand(CliTestCaseGenerator::class, [
                    '--section' => $this->sectionName,
                    '--container' => $this->containerName,
                ], silent: true);
                break;
            case ParentTestCase::WEB_TEST_CASE:
                $this->runGeneratorCommand(WebTestCaseGenerator::class, [
                    '--section' => $this->sectionName,
                    '--container' => $this->containerName,
                ], silent: true);
                break;
        }
    }
}
