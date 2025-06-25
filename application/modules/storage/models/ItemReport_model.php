<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class ItemReport_model extends Conf {
	protected $table = 'item_report';
	protected $primaryKey = 'id';
    public $timestamps = false;
}