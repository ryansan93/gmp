<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class DnDetJurnalTrans_model extends Conf {
	protected $table = 'dn_det_jurnal_trans';
	protected $primaryKey = 'id';
    public $timestamps = false;
}