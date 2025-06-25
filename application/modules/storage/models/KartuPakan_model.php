<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class KartuPakan_model extends Conf{
	public $incrementing = false;

	protected $table = 'kartu_pakan';
	protected $primaryKey = 'id';

	public function barang()
	{
		return $this->hasOne('\Model\Storage\Barang_model', 'kode', 'jns_pakan')->where('tipe', 'pakan')->orderBy('version', 'DESC');
	}

	public function d_supplier()
	{
		return $this->hasOne('\Model\Storage\Supplier_model', 'nomor', 'supplier')->orderBy('version', 'DESC');
	}

	public function kartu_pakan_detail()
	{
		return $this->hasMany('\Model\Storage\KartuPakanDetail_model', 'kartu_pakan', 'id')->with(['d_barang']);
	}

	public function data_rdim_submit()
	{
		return $this->hasOne('\Model\Storage\RdimSubmit_model', 'noreg', 'noreg')->with(['mitra', 'dKandang']);
	}

	public function logs()
	{
		return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table)->orderBy('id', 'ASC');
	}
}
