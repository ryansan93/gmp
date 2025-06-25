<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class SaldoHarian_model extends Conf {
	protected $table = 'saldo_harian';

    public function saldo_harian_det()
	{
		return $this->hasOne('\Model\Storage\SaldoHarianDet_model', 'id_header', 'id')->with(['saldo_harian_det_hutang']);
	}

	public function d_perusahaan()
	{
		return $this->hasOne('\Model\Storage\Perusahaan_model', 'kode', 'perusahaan')->orderBy('id', 'desc');
	}
}