<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class TutupSiklus_model extends Conf {
  	protected $table = 'tutup_siklus';
  	protected $primaryKey = 'id';
    // public $timestamps = false;

    public function potongan_pajak()
  	{
    	return $this->hasOne('\Model\Storage\PotonganPajak_model', 'id', 'id_potongan_pajak');
  	}

    public function logs()
  	{
    	return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table);
  	}
}