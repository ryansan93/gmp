<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class RealisasiPembayaranCn_model extends Conf{
	protected $table = 'realisasi_pembayaran_cn';

	public function det_jurnal()
	{
		return $this->hasOne('\Model\Storage\DetJurnal_model', 'id', 'det_jurnal_id');
	}
}