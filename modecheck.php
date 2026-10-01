<?php
// SEMENTARA - cek apakah APP_MODE terbaca di server. HAPUS setelah selesai dipakai.
header('Content-Type: text/plain; charset=utf-8');
echo 'APP_MODE (SetEnv): ' . (isset($_SERVER['APP_MODE']) ? $_SERVER['APP_MODE'] : '(tidak ada)') . "\n";
echo 'REDIRECT_APP_MODE: ' . (isset($_SERVER['REDIRECT_APP_MODE']) ? $_SERVER['REDIRECT_APP_MODE'] : '(tidak ada)') . "\n";
echo 'URI: ' . $_SERVER['REQUEST_URI'] . "\n";
