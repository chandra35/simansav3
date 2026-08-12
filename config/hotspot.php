<?php

return [
    'radius_dashboard_url' => env('RADIUS_DASHBOARD_URL', 'http://172.16.250.8/daloradius'),
    'coa_port' => (int) env('RADIUS_COA_PORT', 3799),
    'disconnect_timeout' => (float) env('RADIUS_DISCONNECT_TIMEOUT', 8),
    'disconnect_helper' => env('RADIUS_DISCONNECT_HELPER', '/usr/local/sbin/simansa-radius-disconnect'),
];
