<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class KmItem_model extends Conf{
	protected $table = 'kmitem';
	public $timestamps = false;

	public function getKmItem($id = null, $column = 'no_km') {
		$data = null;
        
        $sql_id = "";
        if ( !empty($id) ) {
            $sql_id = "where ki.".$column." in ('".$id."')";
        }

		$sql = "
			select 
				ki.no_km,
				ki.tgl_km,
				ki.no_urut,
				ki.periode,
				ki.no_coa,
				ki.keterangan,
				ki.no_invoice,
				ki.nilai_invoice,
				ki.nilai,
				ki.det_jurnal_trans,
				djt.nama as det_jurnal_trans_nama,
				ki.coa_asal,
				c.nama_coa as coa_asal_nama
			from kmitem ki
			left join
				(
					select djt1.* from det_jurnal_trans djt1
					right join
						(select max(id) as id, kode from det_jurnal_trans group by kode) djt2
						on
							djt1.id = djt2.id
				) djt
				on
					ki.det_jurnal_trans = djt.kode
			left join
				km k
				on
					ki.no_km = k.no_km
            left join
                coa c
                on
                    ki.coa_asal = c.coa
			".$sql_id."
			order by
				ki.no_urut asc
		";
		$d_ki = $this->hydrateRaw($sql);

        if ( !empty($d_ki) && $d_ki->count() > 0 ) {
            $data = $d_ki->toArray();
        }

		return $data;
	}
}
