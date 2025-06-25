<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class PerwakilanMaping_model extends Conf{
	protected $table = 'perwakilan_maping';

	public function dHitungBudidayaItem()
	{
		return $this->hasOne('\Model\Storage\HitungBudidayaItem_model', 'id', 'id_hbi')->with(['dSapronakKesepakatan']);
	}

	public function hitung_budidaya_item_kpm()
	{
		return $this->hasOne('\Model\Storage\HitungBudidayaItem_model', 'id', 'id_hbi')->with(['dSapronakKesepakatanNoCheck']);
	}
}
