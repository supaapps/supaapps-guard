<?php
/*
 * supaapps/supaapps-guard package
 */
return [
    'auth_server_url' => env('SUPAAPPS_GUARD_AUTH_SERVER_URL', 'http://localhost:8000/'),
    'realm_name' => env('SUPAAPPS_GUARD_AUTH_REALM_NAME', 'root'),
];
