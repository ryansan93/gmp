<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class DetGroup_model extends Conf {
	protected $table = 'detail_group';
	protected $primaryKey = 'id_detgroup';
	protected $kodeTable = 'DGR';
    public $timestamps = false;

    public function detail_fitur()
	{
		return $this->hasOne('\Model\Storage\DetFitur_model', 'id_detfitur', 'id_detfitur')
					->with(['fitur'])
					->orderBy('id_detfitur', 'ASC');
	}
}