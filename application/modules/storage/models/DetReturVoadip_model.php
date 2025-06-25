<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class DetReturVoadip_model extends Conf {
	protected $table = 'det_retur_voadip';
	protected $primaryKey = 'id';
    public $timestamps = false;

    public function d_barang()
	{
		return $this->hasOne('\Model\Storage\Barang_model', 'kode', 'item')->orderBy('id', 'desc');
	}
}