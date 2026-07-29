<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Super Admin (synthetic account)
    |--------------------------------------------------------------------------
    |
    | This principal is intentionally independent from the users table.
    | It authenticates with a fixed PIN and is the only role allowed to
    | create and activate client licenses.
    |
    */

    'enabled' => (bool) env('SUPER_ADMIN_ENABLED', true),

    'username' => env('SUPER_ADMIN_USERNAME', 'devnapp'),

    'name' => env('SUPER_ADMIN_NAME', 'DevNApp Super Admin'),

    'pin' => env('SUPER_ADMIN_PIN', '009988'),

    /*
    | Sentinel session identifier. Must never collide with a real users.id.
    | Auto-increment IDs start at 1, so 0 is reserved for Super Admin.
    */
    'id' => (int) env('SUPER_ADMIN_ID', 0),

];
