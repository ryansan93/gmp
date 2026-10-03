<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class MsSewa_model extends Conf{
	
	public $table = 'ms_sewa';
	protected $primaryKey = 'id';
	public $timestamps = false;


	public function table_amortisasi_jadwal()
	{
		return $this->hasMany(\Model\Storage\AmortisasiJadwal_model::class, 'kode_transaksi', 'no_sewa');
	}

	public function ms_termin_sewa()
	{
		return $this->hasMany(\Model\Storage\MsSewaTermin_model::class, 'no_sewa', 'no_sewa');
	}

}
