<?php

return [
    'route_middleware' => ['web', 'jpu_is_super_admin'],
    'route_prefix' => 'admin',
    'route_name_prefix' => 'admin.',
    'route_domain' => null,
    'guard' => null,
];
