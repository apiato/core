<?php

declare(strict_types=1);

namespace Apiato\Generator\Traits;

use Apiato\Generator\Generator;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Str;

/**
 * @mixin Generator
 */
trait UIGeneratorTrait
{
    protected function runCallParam(): array
    {
        $sectionName  = $this->sectionName;
        $_sectionName = Str::lower($this->sectionName);

        $containerName  = $this->containerName;
        $_containerName = Str::lower($this->containerName);

        $model  = $this->containerName;
        $models = Pluralizer::plural($model);

        $this->printInfoMessage('Generating README File');
        $this->call('apiato:make:readme', [
            '--section'   => $sectionName,
            '--container' => $containerName,
            '--file'      => 'README',
        ]);

        $this->printInfoMessage('Generating Configuration File');
        $this->call('apiato:make:configuration', [
            '--section'   => $sectionName,
            '--container' => $containerName,
            '--file'      => Str::camel($this->sectionName) . '-' . Str::camel($this->containerName),
        ]);

        $this->printInfoMessage('Generating Model and Repository');
        $this->call('apiato:make:model', [
            '--section'    => $sectionName,
            '--container'  => $containerName,
            '--file'       => $model,
            '--repository' => true,
        ]);

        $this->printInfoMessage('Generating a basic Migration file');
        $this->call('apiato:make:migration', [
            '--section'   => $sectionName,
            '--container' => $containerName,
            '--file'      => 'create_' . Str::snake($models) . '_table',
            '--tablename' => Str::snake($models),
        ]);

        return [
            $sectionName,
            $_sectionName,
            $containerName,
            $_containerName,
            $model,
            $models,
        ];
    }
}
