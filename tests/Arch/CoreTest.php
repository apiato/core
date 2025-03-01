<?php

use Apiato\Core\Repositories\Exceptions\ResourceCreationFailed;
use Apiato\Core\Repositories\Exceptions\ResourceNotFound;

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
        \Apiato\Http\Response::class,
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
