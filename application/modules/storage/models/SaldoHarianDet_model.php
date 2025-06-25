<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class SaldoHarianDet_model extends Conf {
	protected $table = 'saldo_harian_det';

	public function saldo_harian_det_hutang()
	{
		return $this->hasMany('\Model\Storage\SaldoHarianDetHutang_model', 'id_header', 'id')->with(['d_supplier']);
	}
}