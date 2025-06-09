<?php

declare(strict_types=1);

use Apiato\Core\Repositories\Exceptions\ResourceCreationFailed;
use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use Apiato\Http\Response;

arch()->preset()->php();

arch('src')
    ->expect('Apiato')
    ->toUseStrictEquality()
    ->not->toUse('sleep')
    ->not->toUse('usleep');

arch('src - final classes')
    ->expect('Apiato')
    ->classes()->toBeFinal()
    ->ignoring([
        'Apiato\Core',
        'Apiato\Generator',
        'Apiato\Support\Facades',
        Response::class,
    ]);

arch('src/abstract')
    ->expect('Apiato\Core')
    ->classes()->toBeAbstract()->ignoring([
        ResourceCreationFailed::class,
        ResourceNotFound::class,
    ]);

arch('tests')
    ->expect('Workbench\App')
    ->toOnlyBeUsedIn('Tests');
