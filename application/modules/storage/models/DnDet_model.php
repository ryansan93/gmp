<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class DnDet_model extends Conf {
	protected $table = 'dn_det';
	protected $primaryKey = 'id';
    public $timestamps = false;
}