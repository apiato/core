<?php

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
        'Apiato\Abstract',
        'Apiato\Generator',
        'Apiato\Support\Facades',
    ]);

arch('src/abstract')
    ->expect('Apiato\Abstract')
    ->classes()->toBeAbstract();

arch('tests')
    ->expect('Workbench\App')
    ->toOnlyBeUsedIn('Tests');
