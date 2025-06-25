<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class HarianKandang_model extends Conf{
	protected $table = 'harian_kandang';
	protected $primaryKey = 'id';

	public function detail()
	{
		return $this->hasMany('\Model\Storage\HarianKandangBb_model', 'id_hk', 'id');
	}

	public function dRdimSubmit()
	{
		return $this->hasOne('\Model\Storage\RdimSubmit_model','id','id_rdim_submit')->with(['dMitraMapping', 'dKandang', 'dHitungBudidaya', 'dTimpanen', 'dHarianKandang', 'dRealDocin', 'data_perusahaan']);
	}

	public function logs()
  	{
    	return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table);
  	}
}
