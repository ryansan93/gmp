<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class Coa_model extends Conf{
	protected $table = 'coa';
	protected $primaryKey = 'id';
	public $timestamps = false;

	public function d_perusahaan()
	{
		return $this->hasOne('\Model\Storage\Perusahaan_model', 'kode', 'id_perusahaan')->orderBy('version', 'desc');
	}

	public function logs()
	{
		return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table);
	}
}
