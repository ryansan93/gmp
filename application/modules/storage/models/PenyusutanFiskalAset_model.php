<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class PenyusutanFiskalAset_model extends Conf{
	
	public $table = 'penyusutan_fiskal_aset';
	protected $primaryKey = 'id';
	public $timestamps = false;

}
