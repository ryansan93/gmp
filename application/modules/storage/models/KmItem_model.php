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
				case
					when ki.no_faktur is not null and ki.no_faktur <> '' then
						case
							when ki.keterangan is null or ki.keterangan = '' then
								'PELUNASAN PIUTANG A.N '+cust.nama_cust+' / '+ki.no_faktur
							else
								ki.keterangan
						end
					else
						ki.keterangan
				end as keterangan,
				ki.no_faktur,
				ki.nilai_faktur,
				ki.nilai,
                c.nama_coa
			from kmitem ki
			left join
				km k
				on
					ki.no_km = k.no_km
            left join
                coa c
                on
                    ki.no_coa = c.no_coa
			left join
				customer cust
				on
					k.kode_cust = cust.kode_cust
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
