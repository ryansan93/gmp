<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Client HTTP lintas-instance GMP (intercompany).
 *
 * Tidak ada Guzzle/HTTP-client lain di composer.json app ini, jadi sengaja pakai
 * cURL native supaya tidak menambah dependency baru. Autentikasi pakai HMAC-SHA256
 * atas (body JSON + timestamp) dengan shared secret per partner (application/config,
 * konsisten dgn konvensi kredensial plaintext yang sudah ada di env.php) - tidak ada
 * pola API-key/HMAC lain di codebase ini untuk dipakai ulang, ini yang pertama.
 */
class IntercompanyClient {

    const TIMEOUT_DETIK = 30;
    const SKEW_MAKSIMAL_DETIK = 300; // 5 menit, proteksi replay

    /**
     * Kirim POST bertanda-tangan HMAC ke partner. Return array:
     * ['success' => bool, 'http_code' => int, 'body' => array|null, 'error' => string|null]
     */
    public function post($base_url, $shared_secret, $path, array $payload)
    {
        $body = json_encode($payload);
        $timestamp = (string) time();
        $signature = self::buatSignature($body, $timestamp, $shared_secret);

        $url = rtrim($base_url, '/') . '/' . ltrim($path, '/');

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST            => true,
            CURLOPT_POSTFIELDS      => $body,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_TIMEOUT         => self::TIMEOUT_DETIK,
            CURLOPT_HTTPHEADER      => array(
                'Content-Type: application/json',
                'X-Signature: ' . $signature,
                'X-Timestamp: ' . $timestamp,
            ),
        ));

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return array('success' => false, 'http_code' => 0, 'body' => null, 'error' => 'cURL error: ' . $curl_error);
        }

        $decoded = json_decode($response, true);

        if ($http_code < 200 || $http_code >= 300) {
            return array('success' => false, 'http_code' => $http_code, 'body' => $decoded, 'error' => 'HTTP ' . $http_code . ': ' . $response);
        }

        return array('success' => true, 'http_code' => $http_code, 'body' => $decoded, 'error' => null);
    }

    public static function buatSignature($body, $timestamp, $shared_secret)
    {
        return hash_hmac('sha256', $body . $timestamp, $shared_secret);
    }

    /**
     * Verifikasi signature + timestamp request masuk. $body harus raw JSON string
     * PERSIS seperti yang diterima (jangan di-decode-encode ulang, hasilnya bisa beda).
     */
    public static function verifikasi($body, $timestamp, $signature, $shared_secret)
    {
        if (empty($timestamp) || empty($signature)) {
            return array('valid' => false, 'pesan' => 'Signature/timestamp tidak ada.');
        }

        if (abs(time() - (int) $timestamp) > self::SKEW_MAKSIMAL_DETIK) {
            return array('valid' => false, 'pesan' => 'Timestamp kadaluarsa (kemungkinan replay).');
        }

        $expected = self::buatSignature($body, $timestamp, $shared_secret);

        if (!hash_equals($expected, $signature)) {
            return array('valid' => false, 'pesan' => 'Signature tidak valid.');
        }

        return array('valid' => true, 'pesan' => null);
    }
}
