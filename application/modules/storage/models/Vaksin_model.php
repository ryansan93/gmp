<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class Vaksin_model extends Conf {
	protected $table = 'vaksin';
	protected $primaryKey = 'id';

	public function logs()
	{
		return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table);
	}
}