<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class IntercompanyPakanLog_model extends Conf {
	protected $table = 'intercompany_pakan_log';
	protected $primaryKey = 'id';
    public $timestamps = false;

    public function d_partner()
	{
		return $this->hasOne('\Model\Storage\IntercompanyPartner_model', 'kode_partner', 'kode_partner');
	}

	public function d_barang()
	{
		return $this->hasOne('\Model\Storage\Barang_model', 'kode', 'kode_barang');
	}
}
