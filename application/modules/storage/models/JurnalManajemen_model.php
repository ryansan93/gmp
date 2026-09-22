<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class JurnalManajemen_model extends Conf {
	protected $table = 'jurnal_manajemen';
	protected $primaryKey = 'id';
    public $timestamps = false;

    public function det_jurnal_manajemen()
	{
		return $this->hasMany('\Model\Storage\DetJurnalManajemen_model', 'id_header', 'id');
	}
}
