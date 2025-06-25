<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class DetPakanSPM_model extends Conf {
	public $incrementing = false;
	public $timestamps = false;

	protected $table = 'det_pakanspm';
	protected $primaryKey = 'id_detspm';

	public function rencana_kirim()
	{
		return $this->hasOne('\Model\Storage\PakanDetRcnKirim_model', 'id', 'id_detrcnkirim')->with(['d_unit', 'd_rdim_submit']);
	}

	public function terima_pakan()
	{
		return $this->hasOne('\Model\Storage\PakanTerima_model', 'id_detspm', 'id_detspm')->with(['d_ekspedisi']);
	}

	public function pakan_spm()
	{
		return $this->hasOne('\Model\Storage\PakanSPM_model', 'id_spm', 'id_spm');
	}

	public function logs()
  	{
    	return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table);
  	}
}