<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class IntercompanyPartnerGudang_model extends Conf {
	protected $table = 'intercompany_partner_gudang';
	protected $primaryKey = 'id';
    public $timestamps = false;

    public function d_partner()
	{
		return $this->hasOne('\Model\Storage\IntercompanyPartner_model', 'kode_partner', 'kode_partner');
	}
}
