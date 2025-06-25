<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class OrderPakan_model extends Conf {
	// public $incrementing = false;
	public $timestamps = false;

	protected $table = 'order_pakan';
	protected $primaryKey = 'id';
	protected $no_order = 'no_order';

	// public function getNextNomor($kode){
	// 	$id = $this->whereRaw("SUBSTRING(".$this->nomor.",14,2) = cast(right(year(current_timestamp),2) as char(2))")
	// 	           ->selectRaw("'".$kode."'+replace(str(substring(coalesce(max(".$this->nomor."),'0000'),9,4)+1,4), ' ', '0')+'/'+right(year(current_timestamp),2) as nextId")
	// 	           ->first();
	// 	return $id->nextId;
	// }
	public function getNextNomor($kode){
		$id = $this->whereRaw("SUBSTRING(". $this->no_order .",0,(LEN('".$kode."')+1+6)) = '".$kode."'+'/'+cast(right(year(current_timestamp),2) as char(2))+'/'+replace(str(month(getdate()),2),' ',0)")
				   ->selectRaw("'". $kode ."'+'/'+right(year(current_timestamp),2)+'/'+replace(str(month(getdate()),2),' ',0)+replace(str(substring(coalesce(max(". $this->no_order ."),'000'),(LEN('".$kode."')+1+6),3)+1,3), ' ', '0') as nextId")
				   ->first();
		return $id->nextId;
	}

	public function detail()
	{
		return $this->hasMany('\Model\Storage\OrderPakanDetail_model', 'id_header', 'id')->with(['d_barang', 'd_perusahaan']);
	}

	public function d_supplier()
	{
		return $this->hasOne('\Model\Storage\Pelanggan_model', 'nomor', 'supplier')->where('tipe', 'supplier')->orderBy('version', 'desc');
	}

	public function kirim()
	{
		return $this->hasOne('\Model\Storage\KirimPakan_model', 'no_order', 'no_order');
	}

	public function logs()
  	{
    	return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table);
  	}
}