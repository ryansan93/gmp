<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class DetPembayaranPelanggan_model extends Conf{
	protected $table = 'det_pembayaran_pelanggan';

	public function data_do()
	{
		return $this->hasOne('\Model\Storage\DetRealSJ_model', 'id', 'id_do')->with(['header']);
	}
}
