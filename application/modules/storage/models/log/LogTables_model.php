<?php
namespace Model\Storage\Log;
use \Model\Storage\Log\ConfLog as ConfLog;

class LogTables_model extends ConfLog{
	protected $table = 'log_tables';
	public $timestamps = false;
}