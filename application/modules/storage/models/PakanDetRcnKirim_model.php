<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class PakanDetRcnKirim_model extends Conf {
	public $incrementing = false;
	public $timestamps = false;

	protected $table = 'pakan_det_rcnkirim';
	protected $primaryKey = 'id';

	public function d_unit()
	{
		return $this->hasOne('\Model\Storage\Wilayah_model', 'id', 'unit');
	}

	public function d_barang()
	{
		return $this->hasOne('\Model\Storage\Barang_model', 'kode', 'pakan')->where('tipe', 'pakan')->orderBy('version', 'DESC');
	}

	public function d_ekspedisi()
	{
		return $this->hasOne('\Model\Storage\Supplier_model', 'nomor', 'ekspedisi')->where('jenis', 'ekspedisi')->where('tipe', 'supplier')->orderBy('version', 'DESC');
	}

	public function d_rdim_submit()
	{
		return $this->hasOne('\Model\Storage\RdimSubmit_model', 'noreg', 'noreg')->with(['mitra', 'dKandang']);
	}

	public function det_pakanspm()
	{
		return $this->hasOne('\Model\Storage\DetPakanSPM_model', 'id_detrcnkirim', 'id')->with(['terima_pakan', 'pakan_spm']);
	}

	// public function get_noreg_not_terima()
	// {
	// 	return $this->hasOne('\Model\Storage\DetPakanSPM_model', 'id_detrcnkirim', 'id')->whereNotIn('id_detspm', function($query){
	// 		$query->select('id_detspm')->from('pakan_terima');
	// 	});
	// }

	public function logs()
  	{
    	return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table);
  	}
}