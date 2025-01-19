<?php

arch()->preset()->php();

// pest()->presets()->custom('apiato', function () {
//    return [
//        expect('Infrastructure')->toOnlyBeUsedIn('Application'),
//        expect('Domain')->toOnlyBeUsedIn('Application'),
//    ];
// });

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
