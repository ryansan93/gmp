<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class StokManajemen_model extends Conf {
	protected $table = 'stok_manajemen';
	protected $primaryKey = 'id';
    public $timestamps = false;

    public function det_stok_manajemen()
	{
		return $this->hasMany('\Model\Storage\DetStokManajemen_model', 'id_header', 'id');
	}
}
