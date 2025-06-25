<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class UserMobile_model extends Conf {
	protected $table = 'ms_user_mobile';
	protected $primaryKey = 'id_user';
	protected $kodeTable = 'USR';
    public $timestamps = false;
}