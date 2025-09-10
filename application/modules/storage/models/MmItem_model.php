<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class MmItem_model extends Conf{
	protected $table = 'mmitem';
	public $timestamps = false;

	public function getMmItem($id = null, $column = 'no_mm') {
		$data = null;
        
        $sql_id = "";
        if ( !empty($id) ) {
            $sql_id = "where mi.".$column." in ('".$id."')";
        }

		$sql = "
			select 
				mi.*,
                c.nama_coa,
				b.no_inv
			from mmitem mi
            left join
                coa c
                on
                    mi.no_coa = c.no_coa
			left join
				beli b
				on
					b.no_lpb = mi.no_lpb
			".$sql_id."
			order by
				mi.no_urut asc
		";
		$d_mi = $this->hydrateRaw($sql);

        if ( !empty($d_mi) && $d_mi->count() > 0 ) {
            $data = $d_mi->toArray();
        }

		return $data;
	}
}
