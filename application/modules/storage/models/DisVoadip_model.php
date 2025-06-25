<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class DisVoadip_model extends Conf {
	public $incrementing = false;
	public $timestamps = false;

	protected $table = 'dis_voadip';
	protected $primaryKey = 'id';

	public function detail()
	{
		return $this->hasMany('\Model\Storage\DisVoadipDetail_model', 'id_order', 'id')->with(['d_barang']);
	}

	public function logs()
  	{
    	return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table);
  	}
}