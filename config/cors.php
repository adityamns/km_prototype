<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['/*'],

    'allowed_methods' => ['POST', 'GET', 'OPTIONS', 'PUT', 'DELETE'],

    'allowed_origins' => [
                            'http://localhost:4200',
                            'http://localhost:3001',
                            'https://checkin.telkomuniversity.ac.id',
                            'https://checkin-employee.telkomuniversity.ac.id',
                            'https://situ-keu.telkomuniversity.ac.id',
                            'https://situ-btp.telkomuniversity.ac.id',
                            'https://situ-kem.telkomuniversity.ac.id',
                            'https://teskepribadian.id/app/tel-u',
                            'https://portal.telkomuniversity.ac.id',
                            'https://dashboard.telkomuniversity.ac.id',
                            'https://dev-aka.telkomuniversity.ac.id',
                            'https://situ-aka.telkomuniversity.ac.id',
                            'https://situ-adm.telkomuniversity.ac.id',
                            'https://onboard.telkomuniversity.ac.id',
                            'https://dokumen-aipt.telkomuniversity.ac.id',
                            'https://situ-sis.telkomuniversity.ac.id',
                            'https://gapura.telkomuniversity.ac.id',
                            'https://situ-spi.telkomuniversity.ac.id',
                            'https://eis.telkomuniversity.ac.id',
                            'https://situ-sps.telkomuniversity.ac.id',
                            'https://dev-adm.telkomuniversity.ac.id',
                            'https://sif.telkomuniversity.ac.id',
                            'https://sppd.telkomuniversity.ac.id',
                            'https://igracias.telkomuniversity.ac.id',
                            'https://celoe.telkomuniversity.ac.id',
                            'https://situ-sdm.telkomuniversity.ac.id',
                            'https://service-v2.telkomuniversity.ac.id',
                            'https://sirama.telkomuniversity.ac.id',
                            'https://sapaalumni.telkomuniversity.ac.id',
                            'https://gate.telkomuniversity.ac.id',
                            'https://login.microsoftonline.com/',
                            'https://graph.microsoft.com',
                            'https://login.microsoftonline.com/*',
                            'https://graph.microsoft.com/*',
                         ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
                            'Access-Control-Allow-Origin',
                            'Accept',
                            'Accept-Language',
                            'Accept-Encoding',
                            'Content-Type',
                            'Content-Length',
                            'Authorization',
                            'Connection',
                            'DNT',
                            'User-Agent',
                         ],

    'exposed_headers' => [],

    'max_age' => 60*5*1,

    'supports_credentials' => false,

];
