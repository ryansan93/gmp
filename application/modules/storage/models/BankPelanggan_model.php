<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class BankPelanggan_model extends Conf {
	public $incrementing = false;

	protected $table = 'bank_pelanggan';
	protected $primaryKey = 'id';

	public function lampiran()
	{
		return $this->hasOne('\Model\Storage\Lampiran_model', 'tabel_id', 'id')->where('tabel', 'bank_pelanggan')->with('d_nama_lampiran');
	}
}