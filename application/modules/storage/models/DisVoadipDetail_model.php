<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class DisVoadipDetail_model extends Conf {
	public $incrementing = false;
	public $timestamps = false;

	protected $table = 'dis_voadip_detail';
	protected $primaryKey = 'id';

	public function d_barang()
	{
		return $this->hasOne('\Model\Storage\Barang_model', 'kode', 'kode_barang')->orderBy('version', 'DESC');
	}
}