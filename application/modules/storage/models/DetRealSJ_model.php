<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class DetRealSJ_model extends Conf{
	protected $table = 'det_real_sj';
	protected $primaryKey = 'id';

	public function header()
	{
		return $this->hasOne('\Model\Storage\RealSJ_model', 'id', 'id_header');
	}
}
