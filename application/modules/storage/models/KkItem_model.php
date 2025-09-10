<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class KkItem_model extends Conf{
	protected $table = 'kkitem';
	public $timestamps = false;

	public function getKkItem($id = null, $column = 'no_kk') {
		$data = null;
        
        $sql_id = "";
        if ( !empty($id) ) {
            $sql_id = "where ki.".$column." in ('".$id."')";
        }

		$sql = "
			select 
				ki.no_kk,
				ki.tgl_kk,
				ki.no_urut,
				ki.periode,
				ki.no_coa,
				case
					when ki.no_lpb is not null and ki.no_lpb <> '' then
						case
							when ki.keterangan is null or ki.keterangan = '' then
								'PELUNASAN HUTANG A.N '+supl.nama_supl+' / '+ki.no_lpb
							else
								ki.keterangan
						end
					else
						ki.keterangan
				end as keterangan,
				ki.no_lpb,
				ki.nilai_lpb,
				ki.nilai,
                c.nama_coa
			from kkitem ki
			left join
				kk k
				on
					ki.no_kk = k.no_kk
            left join
                coa c
                on
                    ki.no_coa = c.no_coa
			left join
				supplier supl
				on
					k.kode_supl = supl.kode_supl
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
