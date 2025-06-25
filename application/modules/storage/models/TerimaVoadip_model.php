<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class TerimaVoadip_model extends Conf {
	// public $incrementing = false;
	public $timestamps = false;

	protected $table = 'terima_voadip';
	protected $primaryKey = 'id';

	public function detail()
	{
		return $this->hasMany('\Model\Storage\TerimaVoadipDetail_model', 'id_header', 'id')->with(['d_barang']);
	}

	public function kirim_voadip()
	{
		return $this->hasOne('\Model\Storage\KirimVoadip_model', 'id', 'id_kirim_voadip')->with(['detail']);
	}

	public function logs()
  	{
    	return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table);
  	}
}