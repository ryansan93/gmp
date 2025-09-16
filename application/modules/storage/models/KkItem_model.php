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
				-- case
				-- 	when ki.no_lpb is not null and ki.no_lpb <> '' then
				-- 		case
				-- 			when ki.keterangan is null or ki.keterangan = '' then
				--				'PELUNASAN HUTANG A.N '+supl.nama_supl+' / '+ki.no_lpb
				--			else
				--				ki.keterangan
				--		end
				--	else
				--		ki.keterangan
				--end as keterangan,
				ki.keterangan,
				ki.no_invoice,
				ki.nilai_invoice,
				ki.nilai,
				ki.det_jurnal_trans,
                -- c.nama_coa
				djt.nama as det_jurnal_trans_nama
			from kkitem ki
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
				kk k
				on
					ki.no_kk = k.no_kk
            -- left join
            --     coa c
            --     on
            --         ki.no_coa = c.no_coa
			-- left join
			-- 	supplier supl
			-- 	on
			-- 		k.kode_supl = supl.kode_supl
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
