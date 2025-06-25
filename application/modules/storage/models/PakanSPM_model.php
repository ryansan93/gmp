<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class PakanSPM_model extends Conf {
	public $incrementing = false;
	public $timestamps = false;

	protected $table = 'pakan_spm';
	protected $primaryKey = 'id_spm';
	protected $noSpm = 'no_spm';
	protected $kodeTable = 'SPM';

	public function getNextId(){
		$id = $this->whereRaw("SUBSTRING(".$this->noSpm.",4,4) = cast(right(year(current_timestamp),2) as char(2))+replace(str(month(getdate()),2),' ',0)")
								->selectRaw("'".$this->kodeTable."'+right(year(current_timestamp),2)+replace(str(month(getdate()),2),' ',0)+replace(str(substring(coalesce(max(".$this->noSpm."),'000'),8,3)+1,3), ' ', '0') as nextId")
								->first();
		return $id->nextId;
	}

	public function detail()
	{
		return $this->hasMany('\Model\Storage\DetPakanSPM_model', 'id_spm', 'id_spm')->with(['rencana_kirim']);
	}

	public function logs()
  	{
    	return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table);
  	}

  	public function ekspedisi()
	{
		return $this->hasOne('\Model\Storage\Supplier_model', 'nomor', 'ekspedisi')->where('jenis', 'ekspedisi')->where('tipe', 'supplier')->orderBy('version', 'DESC');
	}
}