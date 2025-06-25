<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class Gudang_model extends Conf {
	protected $table = 'gudang';
	protected $primaryKey = 'id';
    public $timestamps = false;

    public function dUnit()
	{
		return $this->hasOne('\Model\Storage\Wilayah_model', 'id', 'unit');
	}

	public function d_perusahaan()
	{
		return $this->hasOne('\Model\Storage\Perusahaan_model', 'kode', 'perusahaan')->orderBy('id', 'desc');
	}

    public function logs()
	{
		return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table);
	}
}