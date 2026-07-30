<?php

/**
 * Klien referensi untuk endpoint cipher RDM.
 *
 * Kredensial dan URL wajib berasal dari environment.
 */

function decryptRdmBatch(array $encryptedValues): ?array
{
    $baseUrl = rtrim((string) getenv('RDM_CIPHER_URL'), '/');
    $token = (string) getenv('RDM_CIPHER_TOKEN');

    if ($baseUrl === '' || $token === '' || $encryptedValues === []) {
        return $encryptedValues === [] ? [] : null;
    }

    $handle = curl_init();
    curl_setopt_array($handle, [
        CURLOPT_URL => $baseUrl . '?token=' . rawurlencode($token),
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode(array_values($encryptedValues)),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $response = curl_exec($handle);
    $status = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    $error = curl_error($handle);
    curl_close($handle);

    if ($error !== '' || $status !== 200 || !is_string($response)) {
        return null;
    }

    $decoded = json_decode($response, true);

    if (!is_array($decoded) || count($decoded) !== count($encryptedValues)) {
        return null;
    }

    return array_values($decoded);
}

/**
 * Rekomendasi produksi:
 * - deduplikasi ciphertext sebelum request;
 * - chunk maksimum 25;
 * - maksimum 5 chunk paralel;
 * - cache hanya jika plaintext tidak kosong dan berbeda dari ciphertext;
 * - jangan cache hasil null, timeout, atau response dengan panjang berbeda.
 */
