<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class NoBukti_model extends Conf{
	protected $table = 'no_bukti';

	// public function getNextId(){
	// 	$id = $this->whereRaw("SUBSTRING(kode,4,4) = cast(right(year(current_timestamp),2) as char(2))+replace(str(month(getdate()),2),' ',0)")
	// 							->selectRaw("'AJI'+right(year(current_timestamp),2)+replace(str(month(getdate()),2),' ',0)+replace(str(substring(coalesce(max(kode),'000'),8,3)+1,3), ' ', '0') as nextId")
	// 							->first();
	// 	return $id->nextId;
	// }
}
