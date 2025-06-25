<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class TerimaPeralatan_model extends Conf {
	// public $incrementing = false;
	public $timestamps = false;

	protected $table = 'terima_peralatan';
	protected $primaryKey = 'id';

	public function detail()
	{
		return $this->hasMany('\Model\Storage\TerimaPeralatanDetail_model', 'id_header', 'id')->with(['d_barang']);
	}

	public function logs()
  	{
    	return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table);
  	}
}