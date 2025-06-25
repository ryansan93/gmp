<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class DetStok_model extends Conf {
	protected $table = 'det_stok';
	protected $primaryKey = 'id';
    public $timestamps = false;

    public function d_barang()
	{
		return $this->hasOne('\Model\Storage\Barang_model', 'kode', 'kode_barang');
	}

	public function d_gudang()
	{
		return $this->hasOne('\Model\Storage\Gudang_model', 'id', 'kode_gudang');
	}

	public function det_stok_trans()
	{
		return $this->hasMany('\Model\Storage\DetStokTrans_model', 'id_header', 'id');
	}
}