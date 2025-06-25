<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class DetHargaSapronak_model extends Conf{
	protected $table = 'det_harga_sapronak';
	protected $primaryKey = 'id';

	public function d_barang()
	{
		return $this->hasOne('\Model\Storage\Barang_model', 'kode', 'kode_brg')->orderBy('version', 'desc');
	}
}
