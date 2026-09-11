<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class AmortisasiJadwal_model extends Conf{
	
	public $table = 'tabel_amortisasi_jadwal';
	protected $primaryKey = 'id';
	public $timestamps = false;

}
