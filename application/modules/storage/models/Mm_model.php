<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class Mm_model extends Conf{
	public $table = 'mm';
	protected $primaryKey = 'no_mm';
	public $timestamps = false;

	public function getKode($kode){
		$id = $this->whereRaw("SUBSTRING(".$this->primaryKey.", 0, ".((strlen($kode)+1)+6).") = '".$kode."'+cast(right(year(current_timestamp),2) as char(2))+replace(str(month(getdate()),2),' ',0)+replace(str(day(getdate()),2),' ',0)")
								->selectRaw("'".$kode."'+right(year(current_timestamp),2)+replace(str(month(getdate()),2),' ',0)+replace(str(day(getdate()),2),' ',0)+replace(str(substring(coalesce(max(".$this->primaryKey."),'0000'), ".((strlen($kode)+1)+6).", 4)+1, 4), ' ', '0') as nextId")
								->first();
		return $id->nextId;
	}

	public function getMm($id = null, $column = 'no_mm') {
		$data = null;
        
        $sql_id = "";
        if ( !empty($id) ) {
            $sql_id = "where m.".$column." = '".$id."'";
        }

		$sql = "
			select 
				m.*,
				cust.nama_cust,
				supl.nama_supl
			from mm m
			left join
				customer cust
				on
					m.kode_cust = cust.kode_cust
            left join
                supplier supl
                on
                    m.kode_supl = supl.kode_supl
			".$sql_id."
			order by
				m.tgl_mm desc,
				m.no_mm desc
		";
		$d_mm = $this->hydrateRaw($sql);

        if ( !empty($d_mm) && $d_mm->count() > 0 ) {
            $data = $d_mm->toArray();
        }

		return $data;
	}

	public function getMmByDate($start_date, $end_date) {
		$data = null;

		$sql = "
			select 
				m.*,
				cust.nama_cust,
                supl.nama_supl
			from mm m
			left join
				customer cust
				on
					m.kode_cust = cust.kode_cust
            left join
                supplier supl
                on
                    m.kode_supl = supl.kode_supl
			where
				m.tgl_mm between '".$start_date."' and '".$end_date."'
			order by
				m.tgl_mm desc,
				m.no_mm desc
		";
		$d_mm = $this->hydrateRaw($sql);

        if ( !empty($d_mm) && $d_mm->count() > 0 ) {
            $data = $d_mm->toArray();
        }

		return $data;
	}

	public function getMmCetak($id = null, $column = 'no_mm') {
		$data = null;
        
        $sql_id = "";
        if ( !empty($id) ) {
            $sql_id = "where m.".$column." = '".$id."'";
        }

		$sql = "
			select 
				m.*,
				cust.nama_cust,
				supl.nama_supl,
				mi.no_urut,
				mi.keterangan,
				mi.debet,
				mi.kredit,
				mi.no_faktur,
				mi.no_lpb,
				mi.no_coa,
				c.nama_coa
			from mmitem mi
			left join
				mm m
				on
					mi.no_mm = m.no_mm
			left join
				customer cust
				on
					m.kode_cust = cust.kode_cust
            left join
                supplier supl
                on
                    m.kode_supl = supl.kode_supl
			left join
				coa c
				on
					mi.no_coa = c.no_coa
			".$sql_id."
			order by
				m.tgl_mm desc,
				m.no_mm desc
		";
		$d_mm = $this->hydrateRaw($sql);

        if ( !empty($d_mm) && $d_mm->count() > 0 ) {
            $data = $d_mm->toArray();
        }

		return $data;
	}
}
