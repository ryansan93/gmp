<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class DetJurnalManajemen_model extends Conf {
	protected $table = 'det_jurnal_manajemen';
	protected $primaryKey = 'id';
    public $timestamps = false;

    public function d_supplier()
	{
		return $this->hasOne('\Model\Storage\Supplier_model', 'nomor', 'supplier');
	}

	public function d_perusahaan()
	{
		return $this->hasOne('\Model\Storage\Perusahaan_model', 'kode', 'perusahaan');
	}

	public function d_intercompany_log()
	{
		return $this->hasOne('\Model\Storage\IntercompanyPakanLog_model', 'id', 'id_intercompany_log');
	}
}
