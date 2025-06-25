<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class KirimVoadip_model extends Conf {
	// public $incrementing = false;
	public $timestamps = false;

	protected $table = 'kirim_voadip';
	protected $primaryKey = 'id';
	protected $no_order = 'no_order';
	protected $no_sj = 'no_sj';

	public function getNextIdOrder($kode){
		$id = $this->whereRaw("SUBSTRING(". $this->no_order .",0,(LEN('".$kode."')+1+6)) = '".$kode."'+'/'+cast(right(year(current_timestamp),2) as char(2))+'/'+replace(str(month(getdate()),2),' ',0)")
				   ->selectRaw("'". $kode ."'+'/'+right(year(current_timestamp),2)+'/'+replace(str(month(getdate()),2),' ',0)+replace(str(substring(coalesce(max(". $this->no_order ."),'000'),(LEN('".$kode."')+1+6),3)+1,3), ' ', '0') as nextId")
				   ->first();
		return $id->nextId;
	}

	public function getNextIdSj($kode){
		$id = $this->whereRaw("SUBSTRING(". $this->no_sj .",0,(LEN('".$kode."')+1+6)) = '".$kode."'+'/'+cast(right(year(current_timestamp),2) as char(2))+'/'+replace(str(month(getdate()),2),' ',0)")
				   ->selectRaw("'". $kode ."'+'/'+right(year(current_timestamp),2)+'/'+replace(str(month(getdate()),2),' ',0)+replace(str(substring(coalesce(max(". $this->no_sj ."),'000'),(LEN('".$kode."')+1+6),3)+1,3), ' ', '0') as nextId")
				   ->first();
		return $id->nextId;
	}

 	//	public function getNextIdOrder($kode){
	// 	$id = $this->whereRaw("SUBSTRING(". $this->no_order .",0,(LEN('".$kode."')+1+4)) = '".$kode."'+cast(right(year(current_timestamp),2) as char(2))+replace(str(month(getdate()),2),' ',0)")
	// 			   ->selectRaw("'". $kode ."'+right(year(current_timestamp),2)+replace(str(month(getdate()),2),' ',0)+replace(str(substring(coalesce(max(". $this->no_order ."),'000'),(LEN('".$kode."')+1+4),3)+1,3), ' ', '0') as nextId")
	// 			   ->first();
	// 	return $id->nextId;
	// }

	// public function getNextIdSj($kode){
	// 	$id = $this->whereRaw("SUBSTRING(". $this->no_sj .",0,(LEN('".$kode."')+1+4)) = '".$kode."'+cast(right(year(current_timestamp),2) as char(2))+replace(str(month(getdate()),2),' ',0)")
	// 			   ->selectRaw("'". $kode ."'+right(year(current_timestamp),2)+replace(str(month(getdate()),2),' ',0)+replace(str(substring(coalesce(max(". $this->no_sj ."),'000'),(LEN('".$kode."')+1+4),3)+1,3), ' ', '0') as nextId")
	// 			   ->first();
	// 	return $id->nextId;
	// }

	public function terima()
	{
		return $this->hasOne('\Model\Storage\TerimaVoadip_model', 'id_kirim_voadip', 'id');
	}

	public function retur()
	{
		return $this->hasOne('\Model\Storage\ReturVoadip_model', 'no_order', 'no_order');
	}

	public function detail()
	{
		return $this->hasMany('\Model\Storage\KirimVoadipDetail_model', 'id_header', 'id')->with(['d_barang']);
	}

	public function logs()
	{
		return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table);
	}
}