<?php
namespace Model\Storage\Log;
use \Illuminate\Database\Eloquent\Model as Eloquent;

class ConfLog extends Eloquent
{
	public $timestamps = false;
	public function __construct(){
		$this->setConnection('log');
	}
}