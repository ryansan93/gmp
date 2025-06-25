<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class RhppPiutang_model extends Conf{
	protected $table = 'rhpp_piutang';

    public function piutang()
	{
		return $this->hasOne('\Model\Storage\Piutang_model', 'kode', 'piutang_kode');
	}
}
