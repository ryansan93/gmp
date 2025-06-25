<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class TelpEkspedisi_model extends Conf {
	protected $table = 'telp_ekspedisi';
	protected $primaryKey = 'id';
	public $incrementing = false;
}
