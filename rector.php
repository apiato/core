<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/config',
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/workbench',
    ])
    ->withPhpSets(php82: true)
    ->withTypeCoverageLevel(100)
    ->withDeadCodeLevel(100)
    ->withCodeQualityLevel(100);
