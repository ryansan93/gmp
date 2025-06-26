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
