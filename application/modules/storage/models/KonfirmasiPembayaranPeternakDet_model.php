<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class KonfirmasiPembayaranPeternakDet_model extends Conf{
	protected $table = 'konfirmasi_pembayaran_peternak_det';

	public function detail2()
	{
		return $this->hasMany('\Model\Storage\KonfirmasiPembayaranPeternakDet2_model', 'id_header', 'id');
	}
}