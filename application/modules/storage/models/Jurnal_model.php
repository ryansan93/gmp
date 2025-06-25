<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class Jurnal_model extends Conf{
    // public $incrementing = false;

    protected $table = 'jurnal';
    protected $primaryKey = 'id';

    public function detail()
    {
      	return $this->hasMany('\Model\Storage\DetJurnal_model', 'id_header', 'id')->with(['jurnal_trans_detail', 'jurnal_trans_sumber_tujuan', 'd_supplier', 'd_perusahaan', 'd_unit']);
    }

    public function jurnal_trans()
    {
      	return $this->hasOne('\Model\Storage\JurnalTrans_model', 'id', 'jurnal_trans_id');
    }

    public function d_unit()
    {
        return $this->hasOne('\Model\Storage\Wilayah_model', 'kode', 'unit');
    }
}
