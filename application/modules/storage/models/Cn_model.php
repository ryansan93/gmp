<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class Cn_model extends Conf {
	protected $table = 'cn';
	protected $primaryKey = 'id';
    public $timestamps = false;

    public function getNextNomor($kode)
	{
		$id = $this->whereRaw("SUBSTRING(nomor, LEN('".$kode."')+1, 7) = '/'+cast(right(year(current_timestamp),2) as char(2))+'/'+replace(str(month(getdate()),2),' ',0)+'/'")
                        ->selectRaw("'".$kode."'+'/'+right(year(current_timestamp),2)+'/'+replace(str(month(getdate()),2),' ',0)+'/'+replace(str(substring(coalesce(max(nomor),'000'),((LEN('".$kode."')+1)+(LEN('/'+cast(right(year(current_timestamp),2) as char(2))+'/'+replace(str(month(getdate()),2),' ',0)+'/'))),3)+1,3), ' ', '0') as nextId")
                        ->first();
		return $id->nextId;
	}
}