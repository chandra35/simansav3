<?php

/**
 * Probe referensi untuk dijalankan hanya pada runtime RDM yang sah.
 *
 * Probe ini tidak membongkar source ionCube. Ia hanya menginventarisasi nama
 * fungsi yang tersedia setelah helper resmi dimuat.
 */

define('BASEPATH', true);
require_once __DIR__ . '/../../application/helpers/openssl_helper.php';

$expected = [
    'safe_b64encode',
    'safe_b64decode',
    'ssl_encrypt',
    'ssl_decrypt',
    'enkrip',
    'dekrip',
    'mysql_encrypt',
    'mysql_decrypt',
];

header('Content-Type: application/json');

echo json_encode(array_map(
    static fn (string $function): array => [
        'function' => $function,
        'available' => function_exists($function),
    ],
    $expected
), JSON_PRETTY_PRINT);
