<?php defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| Identitas Perusahaan / Branding
| -------------------------------------------------------------------
| Semua string identitas perusahaan yang dulunya hardcoded tersebar
| di banyak file (title halaman, header login, sidebar, email sender,
| nama cookie session) dipusatkan di sini. Saat clone aplikasi untuk
| perusahaan lain, cukup ubah nilai-nilai di file ini (+ koneksi
| database di env.php) tanpa perlu grep-replace ke banyak file.
*/

$config['company_name']         = 'GMP ERP';
$config['login_heading']        = 'GMP ERP';
$config['judul_aplikasi']       = 'GMP ERP';
// Dikosongkan sengaja - logo.png yang ada di assets/images/ ternyata logo lama
// "Wonokoyo" (bukan logo GMP ERP), jadi favicon/logo dikosongkan dulu sampai
// ada file logo yang benar. Isi lagi dengan path logo yang sesuai saat sudah ada.
$config['logo_path']            = '';
$config['favicon_path']         = '';
$config['sess_cookie_prefix']   = 'gmp';
$config['email_sender_name']    = 'Tim MUS-Premi Ekspedisi';
$config['email_sender_address'] = 'itos@wonokoyo.co.id';
