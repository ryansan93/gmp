<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class PakanRcnKirim_model extends Conf {
	public $incrementing = false;
	public $timestamps = false;

	protected $table = 'pakan_rcnkirim';
	protected $primaryKey = 'id';

	public function logs()
  	{
    	return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table);
  	}
}