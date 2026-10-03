<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class StokSiklusManajemen_model extends Conf {
	protected $table = 'stok_siklus_manajemen';
	protected $primaryKey = 'id';
    public $timestamps = false;

    public function det_stok_siklus_manajemen()
	{
		return $this->hasMany('\Model\Storage\DetStokSiklusManajemen_model', 'id_header', 'id');
	}
}
