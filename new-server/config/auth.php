<?php

return [
    'defaults' => [
        'guard' => 'employees',
        'passwords' => 'employees',
    ],

    /*
    | Authentication Guards
    */
    'guards' => [
        'employees' => [
            'driver' => 'session',
            'provider' => 'employees',
        ],
        'stores' => [
            'driver' => 'session',
            'provider' => 'stores',
        ],
    ],

    /*
    | User Providers
    */
    'providers' => [
        'employees' => [
            'driver' => 'eloquent',
            'model' => App\Models\Employee::class,
        ],
        'stores' => [
            'driver' => 'eloquent',
            'model' => App\Models\Store::class,
        ],
    ],
];
