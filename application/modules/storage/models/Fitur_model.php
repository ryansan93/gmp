<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class Fitur_model extends Conf {
	protected $table = 'ms_fitur';
	protected $primaryKey = 'id_fitur';
	protected $kodeTable = 'FTR';
    public $timestamps = false;

    public function detail_fitur()
	{
		return $this->hasMany('\Model\Storage\DetFitur_model', 'id_fitur', 'id_fitur');
	}
}