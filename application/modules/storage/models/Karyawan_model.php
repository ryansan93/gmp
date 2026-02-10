<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class Karyawan_model extends Conf {
	protected $table = 'karyawan';
	protected $primaryKey = 'id';
	protected $nik = 'nik';

	public function getNextNomor($kode_jenis)
	{
		$id = $this->selectRaw("'".$kode_jenis."'+right(year(current_timestamp),2)+replace(str(substring(coalesce(max(".$this->nik."),'000'),4,3)+1,3), ' ', '0') as nextId")->first();
		return $id->nextId;
	}

	public function unit()
	{
		return $this->hasMany('\Model\Storage\UnitKaryawan_model', 'id_karyawan', 'id');
	}

	public function dWilayah()
	{
		return $this->hasMany('\Model\Storage\WilayahKaryawan_model', 'id_karyawan', 'id');
	}

	public function logs()
	{
		return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table);
	}

	public function getNik( $nama_user )
	{
		$nik = null;

		$sql = "
			select * from karyawan where nama like '".$nama_user."' and status = 1
		";
		$d_sql = $this->hydrateRaw( $sql );

		if ( $d_sql->count() > 0 ) {
			$nik = $d_sql->toArray()[0]['nik'];
		}

		return $nik;
	}

	public function getKaryawanByUserId($userId) {
		$sql_det_user = "
			select * from detail_user where id_user = '".$userId."'
		";
		$d_det_user = $this->hydrateRaw( $sql_det_user );
		
		$data = null;
		if ( $d_det_user->count() > 0 ) {
			$d_det_user = $d_det_user->toArray()[0];
	
			$m_karyawan = new \Model\Storage\Karyawan_model();
			$d_karyawan = $m_karyawan->where('nama', 'like', strtolower(trim($d_det_user['nama_detuser'])).'%')->orderBy('id', 'desc')->first();

			$sql_karyawan = "
			select * from karyawan where nama like '".strtolower(trim(str_replace("'", "''", $d_det_user['nama_detuser'])))."%' order by id desc
			";
			$d_karyawan = $this->hydrateRaw( $sql_karyawan );
	
			if ( $d_karyawan->count() > 0 ) {
				$data = $d_karyawan->toArray();
			}
		}

		return $data;
	}
}