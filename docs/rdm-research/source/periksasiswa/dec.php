<?php

/**
 * Arsip pola endpoint dekripsi RDM.
 *
 * Token aktif tidak disimpan dalam repository. Set RDM_CIPHER_TOKEN pada
 * environment server. File ini membutuhkan helper ionCube asli milik RDM.
 */

error_reporting(0);
header('Content-Type: application/json');

define('BASEPATH', true);
require_once __DIR__ . '/../../application/helpers/openssl_helper.php';

$expectedToken = getenv('RDM_CIPHER_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$expectedToken || !hash_equals($expectedToken, $providedToken)) {
    http_response_code(403);
    echo json_encode(['error' => 'Ditolak']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    http_response_code(422);
    echo json_encode(['error' => 'Input tidak valid']);
    exit;
}

$output = [];

foreach ($input as $encryptedValue) {
    if ($encryptedValue === null || $encryptedValue === '') {
        $output[] = $encryptedValue;
        continue;
    }

    $decryptedValue = mysql_decrypt($encryptedValue);
    $output[] = ($decryptedValue !== false && $decryptedValue !== '')
        ? $decryptedValue
        : $encryptedValue;
}

echo json_encode($output);
