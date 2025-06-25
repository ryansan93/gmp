<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class DaftarKunjungan_model extends Conf {
	protected $table = 'daftar_kunjungan';
	protected $primaryKey = 'id';
	public $timestamps = false;
}