<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class PakanTerima_model extends Conf {
	public $incrementing = false;
	public $timestamps = false;

	protected $table = 'pakan_terima';
	protected $primaryKey = 'id';

	public function d_ekspedisi()
	{
		return $this->hasOne('\Model\Storage\Supplier_model', 'nomor', 'ekspedisi')->where('jenis', 'ekspedisi')->where('tipe', 'supplier')->orderBy('version', 'DESC');
	}

	public function logs()
  	{
    	return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table);
  	}
}