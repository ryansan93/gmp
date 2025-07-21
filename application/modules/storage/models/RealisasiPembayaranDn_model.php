<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class RealisasiPembayaranDn_model extends Conf{
	protected $table = 'realisasi_pembayaran_dn';

	public function det_jurnal()
	{
		return $this->hasOne('\Model\Storage\DetJurnal_model', 'id', 'det_jurnal_id');
	}

	public function d_dn()
	{
		return $this->hasOne('\Model\Storage\Dn_model', 'id', 'id_dn');
	}
}