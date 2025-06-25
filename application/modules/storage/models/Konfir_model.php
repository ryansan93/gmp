<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class Konfir_model extends Conf {
    // public $timestamps = false;
    
	protected $table = 'konfir';
	protected $primaryKey = 'id';

    public function det_konfir()
	{
		return $this->hasMany('\Model\Storage\DetKonfir_model', 'id_konfir', 'id');
	}

	public function logs()
	{
		return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table);
	}

	public function rdim_submit()
	{
		return $this->hasOne('\Model\Storage\RdimSubmit_model', 'noreg', 'noreg')->with(['dKandang', 'dMitraMapping']);
	}

	public function tutup_siklus()
	{
		return $this->hasOne('\Model\Storage\TutupSiklus_model', 'noreg', 'noreg')->with(['logs']);
	}

	public function real_sj()
	{
		return $this->hasOne('\Model\Storage\RealSJ_model','noreg', 'noreg');
	}
}