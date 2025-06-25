<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class DetTimPanen_model extends Conf{
	protected $table = 'det_timpanen';
	protected $primaryKey = 'id_dettim';

	public function dJabatan()
	{
		return $this->hasOne('\Model\Storage\JabatanTimPanen_model', 'id', 'jabatan_dettim');
	}
}
