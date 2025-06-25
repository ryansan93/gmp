<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class RealSJ_model extends Conf{
	protected $table = 'real_sj';
	protected $primaryKey = 'id';

	public function det_real_sj()
	{
		return $this->hasMany('\Model\Storage\DetRealSJ_model', 'id_header', 'id');
	}

	public function logs()
	{
		return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table);
	}

	public function dRdimSubmit()
	{
		return $this->hasOne('\Model\Storage\RdimSubmit_model','noreg', 'noreg')->with(['dMitraMapping']);
	}
}
