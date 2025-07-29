<?php
return [

'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_methods' => ['*'],
'allowed_origins' => ['*'], // for dev, restrict in production
'allowed_headers' => ['*'],
'supports_credentials' => true,
];