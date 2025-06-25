<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class BankEkspedisi_model extends Conf {
	public $incrementing = false;

	protected $table = 'bank_ekspedisi';
	protected $primaryKey = 'id';

	public function lampiran()
	{
		return $this->hasOne('\Model\Storage\Lampiran_model', 'tabel_id', 'id')->where('tabel', 'bank_ekspedisi')->with('d_nama_lampiran');
	}
}