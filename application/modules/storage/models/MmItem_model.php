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
				djt.nama as det_jurnal_trans_nama,
				c_asal.nama_coa as coa_asal_nama,
				c_tujuan.nama_coa as coa_tujuan_nama
			from mmitem mi
			left join
				(
					select djt1.* from det_jurnal_trans djt1
					right join
						(select max(id) as id, kode from det_jurnal_trans group by kode) djt2
						on
							djt1.id = djt2.id
				) djt
				on
					mi.det_jurnal_trans = djt.kode
			left join
				mm m
				on
					mi.no_mm = m.no_mm
            left join
                coa c_asal
                on
                    mi.coa_asal = c_asal.coa
			left join
                coa c_tujuan
                on
                    mi.coa_tujuan = c_tujuan.coa
			-- left join
			-- 	beli b
			-- 	on
			-- 		b.no_lpb = mi.no_lpb
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
