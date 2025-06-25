<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class DetStokTrans_model extends Conf {
	protected $table = 'det_stok_trans';
	protected $primaryKey = 'id';
    public $timestamps = false;

    public function d_barang()
	{
		return $this->hasOne('\Model\Storage\Barang_model', 'kode', 'kode_barang');
	}
}