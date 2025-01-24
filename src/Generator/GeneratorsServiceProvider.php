<?php

namespace Apiato\Generator;

use Apiato\Abstract\Providers\ServiceProvider;
use Apiato\Generator\Commands\ActionGenerator;
use Apiato\Generator\Commands\ConfigurationGenerator;
use Apiato\Generator\Commands\ContainerApiGenerator;
use Apiato\Generator\Commands\ContainerGenerator;
use Apiato\Generator\Commands\ContainerWebGenerator;
use Apiato\Generator\Commands\ControllerGenerator;
use Apiato\Generator\Commands\EventGenerator;
use Apiato\Generator\Commands\EventListenerGenerator;
use Apiato\Generator\Commands\ExceptionGenerator;
use Apiato\Generator\Commands\FunctionalTestGenerator;
use Apiato\Generator\Commands\JobGenerator;
use Apiato\Generator\Commands\MailGenerator;
use Apiato\Generator\Commands\MiddlewareGenerator;
use Apiato\Generator\Commands\MigrationGenerator;
use Apiato\Generator\Commands\ModelFactoryGenerator;
use Apiato\Generator\Commands\ModelGenerator;
use Apiato\Generator\Commands\NotificationGenerator;
use Apiato\Generator\Commands\PolicyGenerator;
use Apiato\Generator\Commands\ReadmeGenerator;
use Apiato\Generator\Commands\RepositoryGenerator;
use Apiato\Generator\Commands\RequestGenerator;
use Apiato\Generator\Commands\RouteGenerator;
use Apiato\Generator\Commands\SeederGenerator;
use Apiato\Generator\Commands\ServiceProviderGenerator;
use Apiato\Generator\Commands\SubActionGenerator;
use Apiato\Generator\Commands\TaskGenerator;
use Apiato\Generator\Commands\TestCaseGenerator;
use Apiato\Generator\Commands\TransformerGenerator;
use Apiato\Generator\Commands\UnitTestGenerator;
use Apiato\Generator\Commands\ValueGenerator;

class GeneratorsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands($this->getGeneratorCommands());
        }
    }

    private function getGeneratorCommands(): array
    {
        return [
            ActionGenerator::class,
            ConfigurationGenerator::class,
            ContainerGenerator::class,
            ContainerApiGenerator::class,
            ContainerWebGenerator::class,
            ControllerGenerator::class,
            EventGenerator::class,
            EventListenerGenerator::class,
            ExceptionGenerator::class,
            JobGenerator::class,
            ModelFactoryGenerator::class,
            MailGenerator::class,
            MiddlewareGenerator::class,
            MigrationGenerator::class,
            ModelGenerator::class,
            NotificationGenerator::class,
            PolicyGenerator::class,
            ReadmeGenerator::class,
            RepositoryGenerator::class,
            RequestGenerator::class,
            RouteGenerator::class,
            SeederGenerator::class,
            ServiceProviderGenerator::class,
            SubActionGenerator::class,
            FunctionalTestGenerator::class,
            TestCaseGenerator::class,
            UnitTestGenerator::class,
            TaskGenerator::class,
            TransformerGenerator::class,
            ValueGenerator::class,
        ];
    }
}
