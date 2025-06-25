<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class RhppGroupHeader_model extends Conf{
	protected $table = 'rhpp_group_header';

	public function rhpp()
	{
		return $this->hasMany('\Model\Storage\RhppGroup_model', 'id_header', 'id')->with(['doc', 'list_noreg', 'pakan', 'oa_pakan', 'pindah_pakan', 'oa_pindah_pakan', 'retur_pakan', 'oa_retur_pakan', 'voadip', 'retur_voadip', 'penjualan', 'potongan', 'bonus', 'piutang']);
	}
}
