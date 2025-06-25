<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class HargaPerforma_model extends Conf{
	protected $table = 'harga_performa';
	protected $primaryKey = 'id';

	public function d_pakan1()
	{
		return $this->hasOne('\Model\Storage\Barang_model', 'kode', 'kode_pakan1')->orderBy('version', 'desc');
	}

	public function d_pakan2()
	{
		return $this->hasOne('\Model\Storage\Barang_model', 'kode', 'kode_pakan2')->orderBy('version', 'desc');
	}

	public function d_pakan3()
	{
		return $this->hasOne('\Model\Storage\Barang_model', 'kode', 'kode_pakan3')->orderBy('version', 'desc');
	}
}
