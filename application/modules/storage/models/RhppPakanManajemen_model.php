<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class RhppPakanManajemen_model extends Conf {
	protected $table = 'rhpp_pakan_manajemen';
	protected $primaryKey = 'id';
	public $timestamps = false;
}
