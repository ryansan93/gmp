<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class DetRpah_model extends Conf {
    // public $timestamps = false;
    
	protected $table = 'det_rpah';

    public function getNextNo($column, $kode){
		$id = $this->whereRaw("SUBSTRING(". $column .",0,(LEN('".$kode."')+1+3)) = '".$kode."'+'/'+cast(right(year(current_timestamp),2) as char(2))")
				   ->selectRaw("'". $kode ."'+'/'+right(year(current_timestamp),2)+'/'+replace(str(substring(coalesce(max(". $column ."),'00000'),(LEN('".$kode."')+1+4),5)+1,5), ' ', '0') as nextId")
				   ->first();
		return $id->nextId;
	}

 //    public function getNextNo($column, $kode){
	// 	$id = $this->whereRaw("SUBSTRING(".$column.",9,2) = cast(right(year(current_timestamp),2) as char(2))")
	// 				->selectRaw("'".$kode."'+replace(str(substring(coalesce(max(".$column."),'00000'),3,5)+1,5), ' ', '0')+'/'+right(year(current_timestamp),2) as nextId")
	// 				->first();
	// 	return $id->nextId;
	// }

    public function data_konfir()
	{
		return $this->hasOne('\Model\Storage\Konfir_model', 'id', 'id_konfir')->with(['det_konfir', 'rdim_submit']);
	}

	public function data_real_sj()
	{
		return $this->hasMany('\Model\Storage\DetRealSJ_model', 'no_do', 'no_do');
	}
}