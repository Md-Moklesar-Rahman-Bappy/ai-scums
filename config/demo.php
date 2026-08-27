<?php

return [

    /*
     * Demo seeder credentials (used only by DatabaseSeeder in local/staging).
     *
     * These values are read from the environment so that config caching never
     * breaks the seeder. A strong random password is generated when no value
     * is supplied, so demo accounts are never created with a guessable default.
     */

    'superadmin_email' => env('DEMO_SUPERADMIN_EMAIL', 'superadmin@iems.test'),
    'superadmin_password' => env('DEMO_SUPERADMIN_PASSWORD'),

    'admin_email' => env('DEMO_ADMIN_EMAIL', 'admin@demo.test'),
    'admin_password' => env('DEMO_ADMIN_PASSWORD'),

];
