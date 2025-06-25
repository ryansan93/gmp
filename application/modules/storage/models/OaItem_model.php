<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class OaItem_model extends Conf{
	protected $table = 'oa_item';
	protected $primaryKey = 'id';

	public function wilayah()
	{
		return $this->hasOne('\Model\Storage\Lokasi_model', 'id', 'wilayah');
	}

	// public function kecamatan()
	// {
	// 	return $this->hasOne('\Model\Storage\Lokasi', 'id', 'kecamatan');
	// }
}
