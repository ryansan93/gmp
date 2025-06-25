<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class LhkSolusi_model extends Conf {
	protected $table = 'lhk_solusi';

	public function d_solusi()
	{
		return $this->hasOne('\Model\Storage\Solusi_model', 'id', 'id_solusi');
	}
}