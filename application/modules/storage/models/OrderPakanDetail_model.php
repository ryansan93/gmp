<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class OrderPakanDetail_model extends Conf {
	public $incrementing = false;
	public $timestamps = false;

	protected $table = 'order_pakan_detail';
	protected $primaryKey = 'id';

	public function d_barang()
	{
		return $this->hasOne('\Model\Storage\Barang_model', 'kode', 'barang')->orderBy('version', 'DESC');
	}

	public function d_perusahaan()
	{
		return $this->hasOne('\Model\Storage\Perusahaan_model', 'kode', 'perusahaan')->orderBy('version', 'DESC');
	}

	public function d_mitra()
	{
		return $this->hasOne('\Model\Storage\Mitra_model', 'nomor', 'kirim')->orderBy('version', 'DESC');
	}
}