<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class RealisasiPembayaranPotongan_model extends Conf{
	protected $table = 'realisasi_pembayaran_potongan';

	public function det_jurnal_trans()
	{
		return $this->hasOne('\Model\Storage\DetJurnalTrans_model', 'id', 'det_jurnal_trans_id');
	}
}