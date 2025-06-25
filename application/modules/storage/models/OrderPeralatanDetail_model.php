<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class OrderPeralatanDetail_model extends Conf {
	public $incrementing = false;
	public $timestamps = false;

	protected $table = 'order_peralatan_detail';
}