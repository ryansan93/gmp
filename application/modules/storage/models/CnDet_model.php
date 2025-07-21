<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class CnDet_model extends Conf {
	protected $table = 'cn_det';
	protected $primaryKey = 'id';
    public $timestamps = false;
}