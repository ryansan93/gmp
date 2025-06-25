<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class OrderDoc_model extends Conf {
	public $incrementing = false;
	public $timestamps = false;

	protected $table = 'order_doc';
	protected $primaryKey = 'id';
	protected $nomor = 'no_order';
	protected $kodeTable = 'ODC';

	public function getNextNomor($kode){
		$id = $this->whereRaw("SUBSTRING(". $this->nomor .",0,(LEN('".$kode."')+1+6)) = '".$kode."'+'/'+cast(right(year(current_timestamp),2) as char(2))+'/'+replace(str(month(getdate()),2),' ',0)")
				   ->selectRaw("'". $kode ."'+'/'+right(year(current_timestamp),2)+'/'+replace(str(month(getdate()),2),' ',0)+replace(str(substring(coalesce(max(". $this->nomor ."),'000'),(LEN('".$kode."')+1+6),3)+1,3), ' ', '0') as nextId")
				   ->first();
		return $id->nextId;
	}

	// public function getNextNomor()
	// {
	// 	$id = $this->selectRaw("'" .$this->kodeTable . "'+right(year(current_timestamp),2)+replace(str(right(month(current_timestamp),1),2), ' ', '0')+replace(str(substring(coalesce(max(".$this->nomor."),'000'),8,3)+1,3), ' ', '0') as nextId")->first();
	// 	return $id->nextId;
	// }

	public function d_barang()
	{
		return $this->hasOne('\Model\Storage\Barang_model', 'kode', 'item');
	}

	public function d_supplier()
	{
		return $this->hasOne('\Model\Storage\Supplier_model', 'nomor', 'supplier');
	}

	public function data_perusahaan()
	{
		return $this->hasOne('\Model\Storage\Perusahaan_model', 'kode', 'perusahaan')->orderBy('id', 'DESC');
	}

	public function terima_doc()
	{
		return $this->hasOne('\Model\Storage\TerimaDoc_model', 'no_order', 'no_order')->orderBy('id', 'DESC');
	}

	public function terima_doc_rhpp()
	{
		return $this->hasOne('\Model\Storage\RealDocin_model', 'noreg', 'noreg')->orderBy('id', 'DESC');
	}

	public function logs()
  	{
    	return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table);
  	}
}