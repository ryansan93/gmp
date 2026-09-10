<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class MasterJenisSewa_model extends Conf{
    
    public $table = 'ms_jenis_sewa';
    protected $primaryKey = 'id';
    public $timestamps = false;

}
