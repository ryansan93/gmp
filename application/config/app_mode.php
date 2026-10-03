<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mode RIIL vs MANAJEMEN - dibedakan dari vhost yang diakses (lihat
 * httpd-vhosts.conf: vhost gml-manajemen.local/gmp-manajemen.local set
 * `SetEnv APP_MODE manajemen`, vhost default/RIIL tidak set apa-apa).
 * Dipakai Model\Storage\Conf utk redirect nama tabel ke versi shadow
 * (_manajemen) & report tertentu (mis. GeneralLedger) yg baca tabel manual.
 */
$config['app_mode'] = (isset($_SERVER['APP_MODE']) && $_SERVER['APP_MODE'] === 'manajemen')
    ? 'manajemen'
    : 'riil';

define('APP_MODE', $config['app_mode']);
