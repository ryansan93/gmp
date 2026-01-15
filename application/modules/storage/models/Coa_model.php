<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class Coa_model extends Conf{
	protected $table = 'coa';
	protected $primaryKey = 'id';
	public $timestamps = false;

	public function d_perusahaan()
	{
		return $this->hasOne('\Model\Storage\Perusahaan_model', 'kode', 'id_perusahaan')->orderBy('version', 'desc');
	}

	public function logs()
	{
		return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table);
	}

	public function getDataCoa() {
		$sql = "
			select 
				c.coa as no_coa,
				c.nama_coa,
				c.unit,
				c.kode
			from coa c
			where
				c.status = 1
			order by
				c.coa asc
		";
		$d_coa = $this->hydrateRaw( $sql );

		$data = null;
		if ( $d_coa->count() > 0 ) {
			$data = $d_coa->toArray();
		}

		return $data;
	}

	public function getDataBank($byUser = 0, $userId = null, $kodeBank = null) {
		$sql_unit = null;

		if ( $byUser == 1 ) {
			$sql_det_user = "
				select * from detail_user where id_user = '".$userId."'
			";
			$d_det_user = $this->hydrateRaw( $sql_det_user );
			
			if ( $d_det_user->count() > 0 ) {
				$d_det_user = $d_det_user->toArray()[0];
		
				$m_karyawan = new \Model\Storage\Karyawan_model();
				$d_karyawan = $m_karyawan->where('nama', 'like', strtolower(trim($d_det_user['nama_detuser'])).'%')->orderBy('id', 'desc')->first();

				$sql_karyawan = "
					select * from karyawan where nama like '".strtolower(trim($d_det_user['nama_detuser']))."%' order by id desc
				";
				$d_karyawan = $this->hydrateRaw( $sql_karyawan );
		
				if ( $d_karyawan->count() > 0 ) {
					$d_karyawan = $d_karyawan->toArray()[0];

					$m_conf = new \Model\Storage\Conf();
					$sql = "
						select
							uk.unit
						from unit_karyawan uk
						where 
							uk.id_karyawan = '".$d_karyawan['id']."' and
							uk.unit like '%all%'
					";
					$d_conf = $m_conf->hydrateRaw( $sql );
			
					if ( $d_conf->count() == 0 ) {
						$kode_unit = array();
			
						$m_conf = new \Model\Storage\Conf();
						$sql = "
							select
								w.kode
							from unit_karyawan uk
							left join
								wilayah w
								on
									uk.unit = w.id
							where 
								uk.id_karyawan = '".$d_karyawan['id']."'
							group by
								w.kode
						";
						$d_uk = $m_conf->hydrateRaw( $sql );
			
						if ( $d_uk->count() > 0 ) {
							$d_uk = $d_uk->toArray();
				
							foreach ($d_uk as $k_uk => $v_uk) {
								$kode_unit[] = $v_uk['kode'];
							}
				
							$sql_unit = "and c.unit in ('".implode("', '", $kode_unit)."')";
						}
					}
				}
			}
		}

		$sql_kode_bank = null;
		if ( !empty($kodeBank) ) {
			$sql_kode_bank = "and c.kode = '".$kodeBank."'";
		}

		$sql = "
			select 
				c.coa as no_coa,
				c.nama_coa,
				c.unit,
				c.kode
			from coa c
			where
				c.bank = 1 and
				c.status = 1
				".$sql_unit."
				".$sql_kode_bank."
			order by
				c.coa
		";
		$d_coa = $this->hydrateRaw( $sql );

		$data = null;
		if ( $d_coa->count() > 0 ) {
			$data = $d_coa->toArray();
		}

		return $data;
	}

	public function getDataKas($byUser = 0, $userId = null, $internal = null) {
		$sql_unit = null;

		$sql_internal = null;
		if ( !empty($internal) ) {
			if ( $internal == 1 ) {
				$sql_internal = "and c.nama_coa like '%internal%'";
			}

			if ( $internal == 2 ) {
				$sql_internal = "and c.nama_coa not like '%internal%'";
			}
		}

		if ( $byUser == 1 ) {
			$sql_det_user = "
				select * from detail_user where id_user = '".$userId."'
			";
			$d_det_user = $this->hydrateRaw( $sql_det_user );
			
			if ( $d_det_user->count() > 0 ) {
				$d_det_user = $d_det_user->toArray()[0];
		
				$m_karyawan = new \Model\Storage\Karyawan_model();
				$d_karyawan = $m_karyawan->where('nama', 'like', strtolower(trim($d_det_user['nama_detuser'])).'%')->orderBy('id', 'desc')->first();

				$sql_karyawan = "
					select * from karyawan where nama like '".strtolower(trim(str_replace("'", "''", $d_det_user['nama_detuser'])))."%' order by id desc
				";
				$d_karyawan = $this->hydrateRaw( $sql_karyawan );
		
				if ( $d_karyawan->count() > 0 ) {
					$d_karyawan = $d_karyawan->toArray()[0];

					$m_conf = new \Model\Storage\Conf();
					$sql = "
						select
							uk.unit
						from unit_karyawan uk
						where 
							uk.id_karyawan = '".$d_karyawan['id']."' and
							uk.unit like '%all%'
					";
					$d_conf = $m_conf->hydrateRaw( $sql );
			
					if ( $d_conf->count() == 0 ) {
						$kode_unit = array();
			
						$m_conf = new \Model\Storage\Conf();
						$sql = "
							select
								w.kode
							from unit_karyawan uk
							left join
								wilayah w
								on
									uk.unit = w.id
							where 
								uk.id_karyawan = '".$d_karyawan['id']."'
							group by
								w.kode
						";
						$d_uk = $m_conf->hydrateRaw( $sql );
			
						if ( $d_uk->count() > 0 ) {
							$d_uk = $d_uk->toArray();
				
							foreach ($d_uk as $k_uk => $v_uk) {
								$kode_unit[] = $v_uk['kode'];
							}
				
							$sql_unit = "and c.unit in ('".implode("', '", $kode_unit)."')";
						}
					}
				}
			}
		}

		$sql = "
			select 
				c.coa as no_coa,
				c.nama_coa,
				c.unit,
				c.kode
			from coa c
			where
				c.kas = 1 and
				c.status = 1
				".$sql_unit."
				".$sql_internal."
			order by
				c.coa
		";
		$d_coa = $this->hydrateRaw( $sql );

		$data = null;
		if ( $d_coa->count() > 0 ) {
			$data = $d_coa->toArray();
		}

		return $data;
	}

	public function getGol1($golongan) {
		$data = null;

		$sql = "
			select 
				c.gol1
			from coa c
			where
				SUBSTRING(c.coa, 1, 1) = '".$golongan."'
			group by
				c.gol1
		";
		$d_coa = $this->hydrateRaw($sql);

        if ( !empty($d_coa) && $d_coa->count() > 0 ) {
            $data = $d_coa->toArray()[0]['gol1'];
        }

		return $data;
	}

	public function getGol2($golongan) {
		$data = null;

		$sql = "
			select 
				c.gol2
			from coa c
			where
				SUBSTRING(c.coa, 1, 2) = '".$golongan."'
			group by
				c.gol2
		";
		$d_coa = $this->hydrateRaw($sql);

        if ( !empty($d_coa) && $d_coa->count() > 0 ) {
            $data = $d_coa->toArray()[0]['gol2'];
        }

		return $data;
	}

	public function getGol3($golongan) {
		$data = null;

		$sql = "
			select 
				c.gol3
			from coa c
			where
				SUBSTRING(c.coa, 1, 4) = '".$golongan."'
			group by
				c.gol3
		";
		$d_coa = $this->hydrateRaw($sql);

        if ( !empty($d_coa) && $d_coa->count() > 0 ) {
            $data = $d_coa->toArray()[0]['gol3'];
        }

		return $data;
	}

	public function getGol4($golongan) {
		$data = null;

		$sql = "
			select 
				c.gol4
			from coa c
			where
				SUBSTRING(c.coa, 1, 7) = '".$golongan."'
			group by
				c.gol4
		";
		$d_coa = $this->hydrateRaw($sql);

        if ( !empty($d_coa) && $d_coa->count() > 0 ) {
            $data = $d_coa->toArray()[0]['gol4'];
        }

		return $data;
	}

	public function getGol5($golongan) {
		$data = null;

		$sql = "
			select 
				c.gol5
			from coa c
			where
				SUBSTRING(c.coa, 1, 10) = '".$golongan."'
			group by
				c.gol5
		";
		$d_coa = $this->hydrateRaw($sql);

        if ( !empty($d_coa) && $d_coa->count() > 0 ) {
            $data = $d_coa->toArray()[0]['gol5'];
        }

		return $data;
	}
}
