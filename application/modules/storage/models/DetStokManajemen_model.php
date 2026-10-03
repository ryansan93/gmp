<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class DetStokManajemen_model extends Conf {
	protected $table = 'det_stok_manajemen';
	protected $primaryKey = 'id';
    public $timestamps = false;

    public function d_barang()
	{
		return $this->hasOne('\Model\Storage\Barang_model', 'kode', 'kode_barang');
	}

	public function d_intercompany_log()
	{
		return $this->hasOne('\Model\Storage\IntercompanyPakanLog_model', 'id', 'id_intercompany_log');
	}

	public function det_stok_trans_manajemen()
	{
		return $this->hasMany('\Model\Storage\DetStokTransManajemen_model', 'id_header', 'id');
	}
}
