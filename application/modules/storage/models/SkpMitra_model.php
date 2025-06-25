<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class SkpMitra_model extends Conf{
	protected $table = 'skp_mitra';
	protected $primaryKey = 'id';

	public function d_mitra()
	{
		return $this->hasOne('\Model\Storage\Mitra_model', 'nomor', 'nomor')->orderBy('version', 'desc');
	}

  	public function lampiran()
	{
		return $this->hasMany('\Model\Storage\Lampiran_model', 'tabel_id', 'id')->where('tabel', $this->table);
	}

	public function logs()
	{
		return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table);
	}
}
