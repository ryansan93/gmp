<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class Stok_model extends Conf {
	protected $table = 'stok';
	protected $primaryKey = 'id';
    public $timestamps = false;

    public function det_stok()
	{
		return $this->hasMany('\Model\Storage\DetStok_model', 'id_header', 'id')->with(['d_barang', 'd_gudang', 'det_stok_trans']);
	}

	public function logs()
	{
		return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table);
	}
}