<?php

// Config extra do projeto. Copie este arquivo para config/organizase.php
// no seu projeto Laravel (ele já é lido automaticamente pelo framework).

return [
    'jwt_secret' => env('JWT_SECRET'),
    'mail_support_to' => env('MAIL_SUPPORT_TO', env('MAIL_USERNAME')),
];
