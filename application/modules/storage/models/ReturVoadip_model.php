<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class ReturVoadip_model extends Conf {
	protected $table = 'retur_voadip';
	protected $primaryKey = 'id';
	protected $no_retur = 'no_retur';
	protected $kode = 'RTV';
    public $timestamps = false;

    public function getNextId(){
		$id = $this->whereRaw("SUBSTRING(". $this->no_retur .",4,4) = cast(right(year(current_timestamp),2) as char(2))+replace(str(month(getdate()),2),' ',0)")
				   ->selectRaw("'". $this->kode ."'+right(year(current_timestamp),2)+replace(str(month(getdate()),2),' ',0)+replace(str(substring(coalesce(max(". $this->no_retur ."),'000'),8,3)+1,3), ' ', '0') as nextId")
				   ->first();
		return $id->nextId;
	}

    public function order_voadip()
	{
		return $this->hasMany('\Model\Storage\OrderVoadip_model', 'no_order', 'no_order');
	}

    public function det_retur_voadip()
	{
		return $this->hasMany('\Model\Storage\DetReturVoadip_model', 'id_header', 'id')->with(['d_barang']);
	}

	public function logs()
	{
		return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table);
	}
}