<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class LogTables_model extends Conf{
  protected $table = 'log_tables';
  protected $primaryKey = 'id';

  public function getLog($tbl_name = null, $tbl_id = null) {
		$data = null;
        
    $d_log = $this->where('tbl_name', $tbl_name)->where('tbl_id', $tbl_id)->orderBy('waktu', 'asc')->get();

    if ( !empty($d_log) && $d_log->count() > 0 ) {
        $data = $d_log->toArray();
    }

		return $data;
	}
}
