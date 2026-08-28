<?php

return [

    'paths' => ['api/*', '*'],

    'allowed_methods' => ['*'],

    // Permite qualquer origem (Expo Web, Celular Físico, Emulador)
    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];