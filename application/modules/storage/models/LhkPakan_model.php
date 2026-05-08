<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class LhkPakan_model extends Conf {
	protected $table = 'lhk_pakan';

	public function pakan()
	{
		return $this->hasOne('\Model\Storage\Barang_model', 'kode', 'kode_barang')->orderBy('id', 'desc');
	}
}