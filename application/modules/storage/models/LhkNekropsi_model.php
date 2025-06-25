<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class LhkNekropsi_model extends Conf {
	protected $table = 'lhk_nekropsi';

	public function d_nekropsi()
	{
		return $this->hasOne('\Model\Storage\Nekropsi_model', 'id', 'id_nekropsi');
	}

	public function foto_nekropsi()
	{
		return $this->hasMany('\Model\Storage\LhkFotoNekropsi_model', 'id_header', 'id');
	}
}