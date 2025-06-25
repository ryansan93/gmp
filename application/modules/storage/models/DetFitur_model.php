<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class DetFitur_model extends Conf {
	protected $table = 'detail_fitur';
	protected $primaryKey = 'id_detfitur';
	protected $kodeTable = 'DFT';
	public $timestamps = false;

    public function fitur()
	{
		return $this->hasOne('\Model\Storage\Fitur_model', 'id_fitur', 'id_fitur');
	}
}