<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class NoBbm_model extends Conf{
	public $table = 'no_bbm';
	public $timestamps = false;

	public function getKode($kode){
		$id = $this->whereRaw("SUBSTRING(kode, 0, ".((strlen($kode)+1)+6).") = '".$kode."'+cast(right(year(current_timestamp),2) as char(2))+replace(str(month(getdate()),2),' ',0)+replace(str(day(getdate()),2),' ',0)")
								->selectRaw("'".$kode."'+right(year(current_timestamp),2)+replace(str(month(getdate()),2),' ',0)+replace(str(day(getdate()),2),' ',0)+replace(str(substring(coalesce(max(kode),'0000'), ".((strlen($kode)+1)+6).", 4)+1, 4), ' ', '0') as nextId")
								->first();
		return $id->nextId;
	}

	public function getKodeKeluar($kode){
		$sql = "
			SELECT 
				case
					when exists( select * from no_bbm where SUBSTRING(kode, 0, (LEN('".$kode."')+1+4)) = '".$kode."'+cast(right(year(current_timestamp),2) as char(2))+replace(str(month(getdate()),2),' ',0) and SUBSTRING(kode, (LEN('".$kode."')+1+4), 1) >= 3 ) then
						'".$kode."'+right(year(current_timestamp),2)+replace(str(month(getdate()),2),' ',0)+replace(str(substring(coalesce(max(kode),'0000'), (LEN('".$kode."')+1+4), 4)+1, 4), ' ', '0')
					else
						'".$kode."'+right(year(current_timestamp),2)+replace(str(month(getdate()),2),' ',0)+replace((3000+str(substring(coalesce(max(kode),'0000'), (LEN('".$kode."')+1+4), 4)+1, 4)), ' ', '0')
				end as nextId
			from no_bbm nb 
			where
				SUBSTRING(kode, 0, (LEN('".$kode."')+1+4)) = '".$kode."'+cast(right(year(current_timestamp),2) as char(2))+replace(str(month(getdate()),2),' ',0) and
				SUBSTRING(kode, (LEN('".$kode."')+1+4), 1) >= 3
		";
		$d_conf = $this->hydrateRaw( $sql );

		$nextId = null;
		if ( $d_conf->count() > 0 ) {
			$nextId = $d_conf->toArray()[0]['nextId'];
		}

		return $nextId;
	}

	public function getKodeMasuk($kode){
		$sql = "
			SELECT 
				case
					when exists( select * from no_bbm where SUBSTRING(kode, 0, (LEN('".$kode."')+1+4)) = '".$kode."'+cast(right(year(current_timestamp),2) as char(2))+replace(str(month(getdate()),2),' ',0) and SUBSTRING(kode, (LEN('".$kode."')+1+4), 1) <= 2 ) then
						'".$kode."'+right(year(current_timestamp),2)+replace(str(month(getdate()),2),' ',0)+replace(str(substring(coalesce(max(kode),'0000'), (LEN('".$kode."')+1+4), 4)+1, 4), ' ', '0')
					else
						'".$kode."'+right(year(current_timestamp),2)+replace(str(month(getdate()),2),' ',0)+replace(str(substring(coalesce(max(kode),'0000'), (LEN('".$kode."')+1+4), 4)+1, 4), ' ', '0')
				end as nextId
			from no_bbm nb 
			where
				SUBSTRING(kode, 0, (LEN('".$kode."')+1+4)) = '".$kode."'+cast(right(year(current_timestamp),2) as char(2))+replace(str(month(getdate()),2),' ',0) and
				SUBSTRING(kode, (LEN('".$kode."')+1+4), 1) <= 2
		";
		$d_conf = $this->hydrateRaw( $sql );

		$nextId = null;
		if ( $d_conf->count() > 0 ) {
			$nextId = $d_conf->toArray()[0]['nextId'];
		}

		return $nextId;
	}

	public function getKodeMasukWithDate($kode, $date){
		$sql = "
			SELECT 
				case
					when exists( select * from no_bbm where SUBSTRING(kode, 0, (LEN('".$kode."')+1+6)) = '".$kode."'+cast(right(year('".$date."'),2) as char(2))+replace(str(month('".$date."'),2),' ',0)+replace(str(day('".$date."'),2),' ',0) and SUBSTRING(kode, (LEN('".$kode."')+1+6), 1) <= 2 ) then
						'".$kode."'+right(year(current_timestamp),2)+replace(str(month('".$date."'),2),' ',0)+replace(str(day('".$date."'),2),' ',0)+replace(str(substring(coalesce(max(kode),'0000'), (LEN('".$kode."')+1+6), 4)+1, 4), ' ', '0')
					else
						'".$kode."'+right(year(current_timestamp),2)+replace(str(month('".$date."'),2),' ',0)+replace(str(day('".$date."'),2),' ',0)+replace(str(substring(coalesce(max(kode),'0000'), (LEN('".$kode."')+1+6), 4)+1, 4), ' ', '0')
				end as nextId
			from no_bbm nb 
			where
				SUBSTRING(kode, 0, (LEN('".$kode."')+1+6)) = '".$kode."'+cast(right(year('".$date."'),2) as char(2))+replace(str(month('".$date."'),2),' ',0)+replace(str(day('".$date."'),2),' ',0) and
				SUBSTRING(kode, (LEN('".$kode."')+1+6), 1) <= 2
		";
		$d_conf = $this->hydrateRaw( $sql );

		$nextId = null;
		if ( $d_conf->count() > 0 ) {
			$nextId = $d_conf->toArray()[0]['nextId'];
		}

		return $nextId;
	}
}
