<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class KartuPakanDetail_model extends Conf{
	public $incrementing = false;
	
	protected $table = 'kartu_pakan_detail';
	protected $primaryKey = 'id';

	public function logs()
	{
		return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table)->orderBy('id', 'ASC');
	}

	public function d_barang()
	{
		return $this->hasOne('\Model\Storage\Barang_model', 'kode', 'jns_pakan')->where('tipe', 'pakan')->orderBy('version', 'DESC');
	}
}