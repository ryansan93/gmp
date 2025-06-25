<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class Group_model extends Conf {
	protected $table = 'ms_group';
	protected $primaryKey = 'id_group';
	protected $kodeTable = 'GRP';
    public $timestamps = false;

    public function detail_group()
	{
		return $this->hasMany('\Model\Storage\DetGroup_model', 'id_group', 'id_group')
					->with(['detail_fitur']);
	}
}