<?php

$defaultCompiledPath = storage_path('framework/views');

return [
    'paths' => [
        resource_path('views'),
    ],

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        is_dir($defaultCompiledPath) && is_writable($defaultCompiledPath)
            ? $defaultCompiledPath
            : sys_get_temp_dir().DIRECTORY_SEPARATOR.'hris-compiled-views'
    ),
];
