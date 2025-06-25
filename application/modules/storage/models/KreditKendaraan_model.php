<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class KreditKendaraan_model extends Conf{
	protected $table = 'kredit_kendaraan';
	protected $primaryKey = 'kode';
	protected $kodeTable = 'KKB';

	public function d_perusahaan()
	{
		return $this->hasOne('\Model\Storage\Perusahaan_model', 'kode', 'perusahaan')->orderBy('id', 'desc');
	}

	public function d_unit()
	{
		return $this->hasOne('\Model\Storage\Wilayah_model', 'kode', 'unit')->orderBy('id', 'desc');
	}

	public function d_peruntukan()
	{
		return $this->hasOne('\Model\Storage\Karyawan_model', 'nik', 'peruntukan')->orderBy('id', 'desc');
	}

  	public function detail()
	{
		return $this->hasMany('\Model\Storage\KreditKendaraanDet_model', 'kredit_kendaraan_kode', 'kode')->orderBy('angsuran_ke', 'asc');
	}

	public function logs()
	{
		return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table)->orderBy('id', 'ASC');
	}
}
