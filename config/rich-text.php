<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Upload disk & directory
    |--------------------------------------------------------------------------
    */
    'disk' => env('RICH_TEXT_DISK', 'public'),
    'directory' => env('RICH_TEXT_DIRECTORY', 'rich-text'),

    /*
    |--------------------------------------------------------------------------
    | Max upload size (kilobytes) and allowed mime extensions
    |--------------------------------------------------------------------------
    */
    'max_kb' => (int) env('RICH_TEXT_MAX_KB', 5120),
    'mimes' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],

    /*
    |--------------------------------------------------------------------------
    | Route middleware (must include web + auth for admin panels)
    |--------------------------------------------------------------------------
    */
    'middleware' => ['web', 'auth'],

];
