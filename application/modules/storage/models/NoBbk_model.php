<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class NoBbk_model extends Conf{
	public $table = 'no_bbk';
	public $timestamps = false;

	public function getKode($kode){
		$id = $this->whereRaw("SUBSTRING(kode, 0, ".((strlen($kode)+1)+6).") = '".$kode."'+cast(right(year(current_timestamp),2) as char(2))+replace(str(month(getdate()),2),' ',0)+replace(str(day(getdate()),2),' ',0)")
								->selectRaw("'".$kode."'+right(year(current_timestamp),2)+replace(str(month(getdate()),2),' ',0)+replace(str(day(getdate()),2),' ',0)+replace(str(substring(coalesce(max(kode),'0000'), ".((strlen($kode)+1)+6).", 4)+1, 4), ' ', '0') as nextId")
								->first();
		return $id->nextId;
	}
}
