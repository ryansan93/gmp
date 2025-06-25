<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class BayarPiutang_model extends Conf{
	protected $table = 'bayar_piutang';
	protected $kode = 'kode';
	protected $status = 'g_status';

	public function getNextId_bpiutang( $_kode ){
		$id = $this->whereRaw("SUBSTRING(". $this->kode .",".(strlen($_kode)+1).",4) = cast(right(year(current_timestamp),2) as char(2))+replace(str(month(getdate()),2),' ',0)")
				   ->whereRaw("SUBSTRING(". $this->kode .",0,".(strlen($_kode)+1).") = '" . $_kode . "'")
				   ->selectRaw("'". $_kode ."'+right(year(current_timestamp),2)+replace(str(month(getdate()),2),' ',0)+replace(str(substring(coalesce(max(". $this->kode ."),'000'),7,3)+1,3), ' ', '0') as nextId")
				   ->first();
		return $id->nextId;
	}

	public function logs()
	{
		return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table)->orderBy('id', 'ASC');
	}
}
