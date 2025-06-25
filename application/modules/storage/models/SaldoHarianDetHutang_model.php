<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class SaldoHarianDetHutang_model extends Conf {
	protected $table = 'saldo_harian_det_hutang';

	public function d_supplier()
	{
		return $this->hasOne('\Model\Storage\Pelanggan_model', 'nomor', 'supplier')->orderBy('id', 'desc');
	}
}