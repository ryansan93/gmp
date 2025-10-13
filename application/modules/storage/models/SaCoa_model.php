<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class SaCoa_model extends Conf{
	public $table = 'sacoa';
	public $timestamps = false;

	public function getSaldoAwal($id = null, $column = null) {
		$data = null;
        
        $sql_id = "";
        if ( !empty($id) ) {
            $sql_id = "where s.".$column." = '".$id."'";
        }

		$sql = "
			select 
				s.*
			from sacoa s
			".$sql_id."
			order by
				s.periode asc,
				s.no_coa asc
		";
		$d_sa = $this->hydrateRaw($sql);

        if ( !empty($d_sa) && $d_sa->count() > 0 ) {
            $data = $d_sa->toArray();
        }

		return $data;
	}
}
