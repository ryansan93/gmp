<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class RhppManajemen_model extends Conf {
	protected $table = 'rhpp_manajemen';
	protected $primaryKey = 'id';
	public $timestamps = false;
}
