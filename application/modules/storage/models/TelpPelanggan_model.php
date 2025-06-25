<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class TelpPelanggan_model extends Conf {
	protected $table = 'telp_pelanggan';
	protected $primaryKey = 'id';
	public $incrementing = false;
}
