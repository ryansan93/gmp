<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class MsAset_model extends Conf{
    
    public $table = 'ms_aset';
    protected $primaryKey = 'id';
    public $timestamps = false;


    public function penyusutan_komersial_aset()
	{
		return $this->hasMany(\Model\Storage\PenyusutanKomersialAset_model::class, 'kode_aset', 'kode_aset');
	}

	public function penyusutan_fiskal_aset()
	{
		return $this->hasMany(\Model\Storage\PenyusutanFiskalAset_model::class, 'kode_aset', 'kode_aset');
	}

}

