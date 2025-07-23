<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class CnDetJurnalTrans_model extends Conf {
	protected $table = 'cn_det_jurnal_trans';
	protected $primaryKey = 'id';
    public $timestamps = false;
}