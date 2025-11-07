<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class NoBbm_model extends Conf{
	public $table = 'no_bbm';
	public $timestamps = false;

	public function getKode($kode, $tanggal){
		$periode = substr(str_replace('-', '', $tanggal), 2, 6);

		$id = $this->whereRaw("SUBSTRING(kode, 0, ".((strlen($kode)+1)+6).") = '".$kode."'+'".$periode."'")
								->selectRaw("'".$kode."'+'".$periode."'+replace(str(substring(coalesce(max(kode),'0000'), ".((strlen($kode)+1)+6).", 4)+1, 4), ' ', '0') as nextId")
								->first();
		return $id->nextId;
	}

	public function getKodeKeluar($kode, $tanggal){
		$periode = substr(str_replace('-', '', $tanggal), 2, 4);
		$sql = "
			SELECT 
				case
					when exists( select * from no_bbm where SUBSTRING(kode, 0, (LEN('".$kode."')+1+4)) = '".$kode."'+'".$periode."' and SUBSTRING(kode, (LEN('".$kode."')+1+4), 1) >= 3 ) then
						'".$kode."'+'".$periode."'+replace(str(substring(coalesce(max(kode),'0000'), (LEN('".$kode."')+1+4), 4)+1, 4), ' ', '0')
					else
						'".$kode."'+'".$periode."'+replace((3000+str(substring(coalesce(max(kode),'0000'), (LEN('".$kode."')+1+4), 4)+1, 4)), ' ', '0')
				end as nextId
			from no_bbm nb 
			where
				SUBSTRING(kode, 0, (LEN('".$kode."')+1+4)) = '".$kode."'+'".$periode."' and
				SUBSTRING(kode, (LEN('".$kode."')+1+4), 1) >= 3
		";
		$d_conf = $this->hydrateRaw( $sql );

		$nextId = null;
		if ( $d_conf->count() > 0 ) {
			$nextId = $d_conf->toArray()[0]['nextId'];
		}

		return $nextId;
	}

	public function getKodeMasuk($kode, $tanggal){
		$periode = substr(str_replace('-', '', $tanggal), 2, 4);
		$sql = "
			SELECT 
				case
					when exists( select * from no_bbm where SUBSTRING(kode, 0, (LEN('".$kode."')+1+4)) = '".$kode."'+'".$periode."' and SUBSTRING(kode, (LEN('".$kode."')+1+4), 1) <= 2 ) then
						'".$kode."'+'".$periode."'+replace(str(substring(coalesce(max(kode),'0000'), (LEN('".$kode."')+1+4), 4)+1, 4), ' ', '0')
					else
						'".$kode."'+'".$periode."'+replace(str(substring(coalesce(max(kode),'0000'), (LEN('".$kode."')+1+4), 4)+1, 4), ' ', '0')
				end as nextId
			from no_bbm nb 
			where
				SUBSTRING(kode, 0, (LEN('".$kode."')+1+4)) = '".$kode."'+'".$periode."' and
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
		$periode = substr(str_replace('-', '', $date), 2, 6);

		$sql = "
			SELECT 
				case
					when exists( select * from no_bbm where SUBSTRING(kode, 0, (LEN('".$kode."')+1+6)) = '".$kode."'+'".$periode."' and SUBSTRING(kode, (LEN('".$kode."')+1+6), 1) <= 2 ) then
						'".$kode."'+'".$periode."'+replace(str(substring(coalesce(max(kode),'0000'), (LEN('".$kode."')+1+6), 4)+1, 4), ' ', '0')
					else
						'".$kode."'+'".$periode."'+replace(str(substring(coalesce(max(kode),'0000'), (LEN('".$kode."')+1+6), 4)+1, 4), ' ', '0')
				end as nextId
			from no_bbm nb 
			where
				SUBSTRING(kode, 0, (LEN('".$kode."')+1+6)) = '".$kode."'+'".$periode."' and
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
