<?php
namespace Model\Storage\Mgberp;
use \Illuminate\Database\Eloquent\Model as Eloquent;

class ConfMgb extends Eloquent
{
	public $timestamps = false;
	public function __construct(){
		$this->setConnection('mgb');
	}

	public function getCurrConnection(){
		return $this->getConnection('mgb');
	}

	public static function factory($nama_class){
		$new_class = '\Model\\Storage\\Mgberp\\'.$nama_class;
		return new $new_class;
	}
}