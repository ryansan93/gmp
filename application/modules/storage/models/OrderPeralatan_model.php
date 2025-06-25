<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class OrderPeralatan_model extends Conf {
	protected $table = 'order_peralatan';
	protected $primaryKey = 'id';
	protected $nomor = 'no_order';

	public function getNextNomor($kode){
		$id = $this->whereRaw("SUBSTRING(". $this->nomor .",0,(LEN('".$kode."')+1+6)) = '".$kode."'+'/'+cast(right(year(current_timestamp),2) as char(2))+'/'+replace(str(month(getdate()),2),' ',0)")
				   ->selectRaw("'". $kode ."'+'/'+right(year(current_timestamp),2)+'/'+replace(str(month(getdate()),2),' ',0)+replace(str(substring(coalesce(max(". $this->nomor ."),'000'),(LEN('".$kode."')+1+6),3)+1,3), ' ', '0') as nextId")
				   ->first();
		return $id->nextId;
	}

	public function logs()
  	{
    	return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table);
  	}
}