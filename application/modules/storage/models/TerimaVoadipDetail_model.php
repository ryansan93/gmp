<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class TerimaVoadipDetail_model extends Conf {
	// public $incrementing = false;
	public $timestamps = false;

	protected $table = 'det_terima_voadip';
	protected $primaryKey = 'id';

	public function d_barang()
	{
		return $this->hasOne('\Model\Storage\Barang_model', 'kode', 'item')->orderBy('version', 'DESC');
	}
}