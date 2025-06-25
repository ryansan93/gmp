<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class TerimaDoc_model extends Conf {
	public $incrementing = false;
	public $timestamps = false;

	protected $table = 'terima_doc';
	protected $primaryKey = 'id';
	protected $nomor = 'no_terima';
	protected $kodeTable = 'TDC';

	public function getNextNomor()
	{
		$id = $this->selectRaw("'" .$this->kodeTable . "'+right(year(current_timestamp),2)+replace(str(right(month(current_timestamp),1),2), ' ', '0')+replace(str(substring(coalesce(max(".$this->nomor."),'000'),8,5)+1,5), ' ', '0') as nextId")->first();
		return $id->nextId;
	}

	public function order_doc()
	{
		return $this->hasOne('\Model\Storage\OrderDoc_model', 'no_order', 'no_order')->orderBy('version', 'DESC');
	}

	public function logs()
  	{
    	return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table);
  	}
}