<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class DetUser_model extends Conf {
	protected $table = 'detail_user';
	protected $primaryKey = 'id_detuser';
	protected $kodeTable = 'DUR';
	public $timestamps = false;

	public function data_group()
	{
		return $this->hasOne('\Model\Storage\Group_model', 'id_group', 'id_group')
					->with(['detail_group']);
	}
}