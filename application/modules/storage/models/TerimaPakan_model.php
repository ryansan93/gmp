<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class TerimaPakan_model extends Conf {
	// public $incrementing = false;
	public $timestamps = false;

	protected $table = 'terima_pakan';
	protected $primaryKey = 'id';

	public function detail()
	{
		return $this->hasMany('\Model\Storage\TerimaPakanDetail_model', 'id_header', 'id')->with(['d_barang']);
	}

	public function kirim_pakan()
	{
		return $this->hasOne('\Model\Storage\KirimPakan_model', 'id', 'id_kirim_pakan')->with(['detail']);
	}

	public function logs()
	{
		return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table)->orderBy('waktu', 'desc');
	}
}