<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class IntercompanyPartner_model extends Conf {
	public $incrementing = false;

	protected $table = 'intercompany_partner';
	protected $primaryKey = 'kode_partner';
    public $timestamps = false;

    public function gudang()
	{
		return $this->hasMany('\Model\Storage\IntercompanyPartnerGudang_model', 'kode_partner', 'kode_partner');
	}
}
