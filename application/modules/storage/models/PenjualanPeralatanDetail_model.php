<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class PenjualanPeralatanDetail_model extends Conf {
	protected $table = 'penjualan_peralatan_detail';

	public function d_barang()
	{
		return $this->hasOne('\Model\Storage\Barang_model', 'kode', 'item')->where('tipe', 'peralatan')->orderBy('version', 'desc');
	}
}