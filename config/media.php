<?php

return [
    'uploads' => [
        // Directory for temporary files
        'temp_path' => 'tmp/',

        // How long pre-signed upload URL is valid
        'expires' => '+60 minutes',

        // Default permissions for uploaded file
        'acl' => 'public-read',
    ],
];
