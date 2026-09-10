<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class MasterSewa_model extends Conf{
	
	public $table = 'ms_sewa';
	protected $primaryKey = 'id';
	public $timestamps = false;

}
