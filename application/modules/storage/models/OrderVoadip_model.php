<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class OrderVoadip_model extends Conf {
	// public $incrementing = false;
	public $timestamps = false;

	protected $table = 'order_voadip';
	protected $primaryKey = 'id';
	protected $no_order = 'no_order';
	protected $kodeTable = 'OVO';

	// public function getNextNomor()
	// {
	// 	$id = $this->selectRaw("'" .$this->kodeTable . "'+right(year(current_timestamp),2)+replace(str(right(month(current_timestamp),1),2), ' ', '0')+replace(str(substring(coalesce(max(".$this->nomor."),'000'),8,3)+1,3), ' ', '0') as nextId")->first();
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
		return $this->hasMany('\Model\Storage\OrderVoadipDetail_model', 'id_order', 'id')->with(['d_barang', 'data_perusahaan']);
	}

	public function d_supplier()
	{
		return $this->hasOne('\Model\Storage\Supplier_model', 'nomor', 'supplier')->where('jenis', '<>', 'ekspedisi')->where('tipe', 'supplier')->orderBy('version', 'DESC');
	}

	public function kirim()
	{
		return $this->hasOne('\Model\Storage\KirimVoadip_model', 'no_order', 'no_order');
	}

	public function logs()
  	{
    	return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table);
  	}
}