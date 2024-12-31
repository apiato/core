<?php

return [
    'laravel/pail' => [
        'providers' => [
            0 => 'Laravel\\Pail\\PailServiceProvider',
        ],
    ],
    'laravel/tinker' => [
        'providers' => [
            0 => 'Laravel\\Tinker\\TinkerServiceProvider',
        ],
    ],
    'nesbot/carbon' => [
        'providers' => [
            0 => 'Carbon\\Laravel\\ServiceProvider',
        ],
    ],
    'nunomaduro/collision' => [
        'providers' => [
            0 => 'NunoMaduro\\Collision\\Adapters\\Laravel\\CollisionServiceProvider',
        ],
    ],
    'nunomaduro/termwind' => [
        'providers' => [
            0 => 'Termwind\\Laravel\\TermwindServiceProvider',
        ],
    ],
    'orchestra/canvas' => [
        'providers' => [
            0 => 'Orchestra\\Canvas\\LaravelServiceProvider',
        ],
    ],
    'orchestra/canvas-core' => [
        'providers' => [
            0 => 'Orchestra\\Canvas\\Core\\LaravelServiceProvider',
        ],
    ],
    'prettus/l5-repository' => [
        'providers' => [
            0 => 'Prettus\\Repository\\Providers\\RepositoryServiceProvider',
        ],
    ],
    'spatie/laravel-fractal' => [
        'aliases' => [
            'Fractal' => 'Spatie\\Fractal\\Facades\\Fractal',
        ],
        'providers' => [
            0 => 'Spatie\\Fractal\\FractalServiceProvider',
        ],
    ],
    'vinkla/hashids' => [
        'aliases' => [
            'Hashids' => 'Vinkla\\Hashids\\Facades\\Hashids',
        ],
        'providers' => [
            0 => 'Vinkla\\Hashids\\HashidsServiceProvider',
        ],
    ],
    'apiato/core' => [
        'providers' => [
            0 => 'Apiato\\Core\\Foundation\\Providers\\ApiatoServiceProvider',
        ],
    ],
];
